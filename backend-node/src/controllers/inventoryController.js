const db = require("../config/database");
const path = require("path");
const fs = require("fs");

/**
 * GET /inventory/assets
 * Full list with optional filters: search, status, condition, label_status, lab_id
 * Accessible to: staf_administrasi, administrator, staf_laboratorium
 */
const getInventoryAssets = async (req, res, next) => {
  try {
    const { search, status, condition, label_status, lab_id } = req.query;

    let whereConditions = [];
    let params = [];

    if (search) {
      whereConditions.push("(ia.asset_code LIKE ? OR ia.label_number LIKE ? OR ic.name LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }

    if (status) {
      whereConditions.push("ia.status = ?");
      params.push(status);
    }

    if (condition) {
      whereConditions.push("ia.asset_condition = ?");
      params.push(condition);
    }

    if (label_status === 'labeled') {
      whereConditions.push("ia.label_number IS NOT NULL AND ia.label_number != ''");
    } else if (label_status === 'unlabeled') {
      whereConditions.push("(ia.label_number IS NULL OR ia.label_number = '')");
    }

    // Filter by lab (via procurement_item → procurement_draft → lab)
    if (lab_id) {
      whereConditions.push(`(
        ia.procurement_item_id IS NOT NULL AND EXISTS (
          SELECT 1 FROM procurement_items pi2
          JOIN procurement_drafts pd2 ON pi2.draft_id = pd2.id
          WHERE pi2.id = ia.procurement_item_id AND pd2.lab_id = ?
        )
      )`);
      params.push(lab_id);
    }

    const whereClause = whereConditions.length > 0
      ? "WHERE " + whereConditions.join(" AND ")
      : "";

    const [assets] = await db.query(`
      SELECT
        ia.id,
        ia.asset_code,
        ia.label_number,
        ia.qr_code,
        ia.barcode,
        ia.serial_number,
        ia.purchase_price,
        ia.purchase_date,
        ia.received_date,
        ia.asset_condition,
        ia.status,
        ia.photo_url,
        ia.notes,
        ia.procurement_item_id,
        ia.receipt_id,
        ia.created_at,
        ia.updated_at,
        ic.name       AS item_name,
        ic.type       AS item_type,
        ic.unit       AS item_unit,
        icat.name     AS category_name,
        r.name        AS room_name,
        r.code        AS room_code,
        pd.lab_id,
        l.name        AS lab_name,
        l.code        AS lab_code
      FROM inventory_assets AS ia
      JOIN item_catalogs AS ic ON ia.item_catalog_id = ic.id
      LEFT JOIN item_categories AS icat ON ic.category_id = icat.id
      LEFT JOIN rooms AS r ON ia.room_id = r.id
      LEFT JOIN procurement_items AS pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories AS l ON pd.lab_id = l.id
      ${whereClause}
      ORDER BY ia.created_at DESC
    `, params);

    res.json({
      success: true,
      data:    assets,
      message: "Data inventaris berhasil diambil"
    });
  } catch (error) {
    console.error("[INVENTORY ASSETS ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil data inventaris",
      errors:  { detail: error.message }
    });
  }
};

/**
 * GET /inventory/assets/:id
 * Single asset detail + enriched with procurement & goods_receipt history
 */
const getInventoryAsset = async (req, res, next) => {
  try {
    const { id } = req.params;

    // Main asset record
    const [rows] = await db.query(`
      SELECT
        ia.id,
        ia.asset_code,
        ia.label_number,
        ia.qr_code,
        ia.barcode,
        ia.serial_number,
        ia.purchase_price,
        ia.purchase_date,
        ia.received_date,
        ia.asset_condition,
        ia.status,
        ia.photo_url,
        ia.notes,
        ia.procurement_item_id,
        ia.receipt_id,
        ia.replaced_by_asset_id,
        ia.created_at,
        ia.updated_at,
        ic.name       AS item_name,
        ic.type       AS item_type,
        ic.unit       AS item_unit,
        icat.name     AS category_name,
        r.name        AS room_name,
        r.code        AS room_code,
        l.name        AS lab_name,
        l.code        AS lab_code
      FROM inventory_assets AS ia
      JOIN item_catalogs AS ic ON ia.item_catalog_id = ic.id
      LEFT JOIN item_categories AS icat ON ic.category_id = icat.id
      LEFT JOIN rooms AS r ON ia.room_id = r.id
      LEFT JOIN procurement_items AS pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories AS l ON pd.lab_id = l.id
      WHERE ia.id = ?
    `, [id]);

    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const asset = rows[0];

    // Enrich: goods_receipt history via procurement_item
    let receiptHistory = [];
    if (asset.procurement_item_id) {
      const [receipts] = await db.query(`
        SELECT
          gr.id,
          gr.received_date,
          gr.quantity_received,
          gr.note,
          gr.created_at,
          u.name AS received_by_name
        FROM goods_receipts AS gr
        JOIN users AS u ON gr.received_by = u.id
        WHERE gr.procurement_item_id = ?
        ORDER BY gr.received_date ASC
      `, [asset.procurement_item_id]);
      receiptHistory = receipts;
    }

    asset.receipt_history = receiptHistory;

    res.json({
      success: true,
      data:    asset,
      message: "Detail aset berhasil diambil"
    });
  } catch (error) {
    console.error("[INVENTORY ASSET DETAIL ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil detail inventaris",
      errors:  { detail: error.message }
    });
  }
};

/**
 * PATCH /inventory/assets/:id/label
 * Update label info for an inventory asset.
 * Accepts JSON body: { asset_code?, label_number, barcode?, photo_url? }
 *
 * After update:
 *   - set status = 'labeled'
 *   - insert asset_condition_logs record
 *
 * Accessible to: staf_administrasi
 */
const updateAssetLabel = async (req, res, next) => {
  const connection = await db.getConnection();
  try {
    await connection.beginTransaction();

    const { id } = req.params;
    const userId = req.user?.id;

    // Accept both JSON body (PATCH) and multipart form (POST with file)
    const label_number = req.body.label_number;
    const asset_code   = req.body.asset_code   || null;
    const barcode      = req.body.barcode       || null;
    let   photo_url    = req.body.photo_url     || null;

    if (!label_number) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: "Nomor label harus diisi",
        errors:  { label_number: "Wajib diisi" }
      });
    }

    // Fetch current asset
    const [assetRows] = await connection.query(
      `SELECT id, label_number, asset_code, status, asset_condition FROM inventory_assets WHERE id = ?`,
      [id]
    );

    if (assetRows.length === 0) {
      await connection.rollback();
      connection.release();
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const asset = assetRows[0];

    // Check label_number uniqueness (skip if same asset)
    const [dupLabel] = await connection.query(
      `SELECT id FROM inventory_assets WHERE label_number = ? AND id != ?`,
      [label_number, id]
    );
    if (dupLabel.length > 0) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: `Nomor label '${label_number}' sudah digunakan oleh aset lain`,
        errors:  { label_number: "Sudah digunakan" }
      });
    }

    // Handle file upload (multipart via old endpoint)
    if (req.file) {
      const uploadsDir = path.join(__dirname, '../../uploads/assets');
      if (!fs.existsSync(uploadsDir)) {
        fs.mkdirSync(uploadsDir, { recursive: true });
      }
      photo_url = `/uploads/assets/${req.file.filename}`;
    }

    // Build dynamic update fields
    const updateFields  = ["label_number = ?", "status = 'labeled'", "updated_at = NOW()"];
    const updateParams  = [label_number];

    if (asset_code) {
      updateFields.push("asset_code = ?");
      updateParams.push(asset_code);
    }
    if (barcode) {
      updateFields.push("barcode = ?");
      updateParams.push(barcode);
    }
    if (photo_url) {
      updateFields.push("photo_url = ?");
      updateParams.push(photo_url);
      // Also update qr_code for backward compat
      updateFields.push("qr_code = ?");
      updateParams.push(photo_url);
    }

    updateParams.push(id);

    await connection.query(
      `UPDATE inventory_assets SET ${updateFields.join(", ")} WHERE id = ?`,
      updateParams
    );

    // Insert asset_condition_logs to record the label assignment
    await connection.query(`
      INSERT INTO asset_condition_logs (inventory_asset_id, updated_by, old_condition, new_condition, note, updated_at)
      VALUES (?, ?, ?, ?, 'Label assigned', NOW())
    `, [id, userId, asset.asset_condition, asset.asset_condition]);

    await connection.commit();
    connection.release();

    res.json({
      success: true,
      message: "Label dan foto berhasil diperbarui. Status diubah ke 'labeled'.",
      data: {
        id,
        label_number,
        asset_code:  asset_code  || asset.asset_code,
        barcode:     barcode     || null,
        photo_url:   photo_url   || null,
        status:      'labeled'
      }
    });
  } catch (error) {
    try { await connection.rollback(); } catch (_) {}
    connection.release();
    console.error("[INVENTORY LABEL UPDATE ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal memperbarui label inventaris",
      errors:  { detail: error.message }
    });
  }
};

/**
 * GET /inventory/assets/:id/timeline
 * Full lifecycle timeline: procurement → receipt → condition_logs → maintenance → disposal
 */
const getAssetTimeline = async (req, res, next) => {
  try {
    const { id } = req.params;

    const [assetRows] = await db.query(
      `SELECT id, asset_code, procurement_item_id, receipt_id FROM inventory_assets WHERE id = ?`,
      [id]
    );

    if (assetRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const asset    = assetRows[0];
    let   timeline = [];

    // 1. Procurement origin
    if (asset.procurement_item_id) {
      const [procRows] = await db.query(`
        SELECT
          pi.item_name, pi.estimated_price, pi.quantity, pi.review_status, pi.reviewed_at,
          pd.title AS draft_title, pd.budget_year, pd.finalized_at,
          uc.name AS created_by_name
        FROM procurement_items AS pi
        JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
        JOIN users AS uc ON pd.created_by = uc.id
        WHERE pi.id = ?
      `, [asset.procurement_item_id]);

      if (procRows.length > 0) {
        const p = procRows[0];
        timeline.push({
          type:        'procurement',
          title:       'Pengadaan',
          description: `Diajukan dalam draf "${p.draft_title}" (${p.budget_year})`,
          detail:      `${p.item_name} — estimasi Rp ${Number(p.estimated_price).toLocaleString('id')} × ${p.quantity}`,
          date:        p.finalized_at,
          user:        p.created_by_name,
          status:      p.review_status
        });
      }
    }

    // 2. All goods_receipt records for this procurement_item
    if (asset.procurement_item_id) {
      const [receipts] = await db.query(`
        SELECT gr.id, gr.received_date, gr.quantity_received, gr.note, u.name AS received_by_name
        FROM goods_receipts AS gr
        JOIN users AS u ON gr.received_by = u.id
        WHERE gr.procurement_item_id = ?
        ORDER BY gr.received_date ASC
      `, [asset.procurement_item_id]);

      receipts.forEach(r => {
        timeline.push({
          type:        'receipt',
          title:       'Penerimaan Barang',
          description: `Diterima ${r.quantity_received} unit`,
          detail:      r.note || null,
          date:        r.received_date,
          user:        r.received_by_name,
          status:      'received'
        });
      });
    }

    // 3. Condition logs
    const [condLogs] = await db.query(`
      SELECT acl.old_condition, acl.new_condition, acl.note, acl.updated_at, u.name AS updated_by_name
      FROM asset_condition_logs AS acl
      JOIN users AS u ON acl.updated_by = u.id
      WHERE acl.inventory_asset_id = ?
      ORDER BY acl.updated_at ASC
    `, [id]);

    condLogs.forEach(log => {
      timeline.push({
        type:        'condition_change',
        title:       'Perubahan Kondisi / Label',
        description: `${log.old_condition || '—'} → ${log.new_condition}`,
        detail:      log.note,
        date:        log.updated_at,
        user:        log.updated_by_name,
        status:      log.new_condition
      });
    });

    // 4. Maintenance logs
    const [maintLogs] = await db.query(`
      SELECT ml.maintenance_date, ml.issue_description, ml.action_taken,
             ml.condition_before, ml.condition_after, ml.status, ml.cost, ml.notes,
             u.name AS performed_by_name
      FROM maintenance_logs AS ml
      JOIN users AS u ON ml.performed_by = u.id
      WHERE ml.inventory_asset_id = ?
      ORDER BY ml.maintenance_date ASC
    `, [id]);

    maintLogs.forEach(log => {
      timeline.push({
        type:        'maintenance',
        title:       'Maintenance',
        description: log.issue_description || 'Pemeliharaan rutin',
        detail:      log.action_taken,
        date:        log.maintenance_date,
        user:        log.performed_by_name,
        status:      log.status,
        cost:        log.cost
      });
    });

    // 5. Disposal
    const [disposalRows] = await db.query(`
      SELECT ad.disposal_date, ad.reason, ad.disposal_note, u.name AS disposed_by_name
      FROM asset_disposals AS ad
      JOIN users AS u ON ad.disposed_by = u.id
      WHERE ad.inventory_asset_id = ?
    `, [id]);

    disposalRows.forEach(row => {
      timeline.push({
        type:        'disposal',
        title:       'Penghapusan Aset',
        description: row.reason,
        detail:      row.disposal_note,
        date:        row.disposal_date,
        user:        row.disposed_by_name,
        status:      'disposed'
      });
    });

    // Sort chronologically
    timeline.sort((a, b) => {
      const da = a.date ? new Date(a.date) : new Date(0);
      const db_ = b.date ? new Date(b.date) : new Date(0);
      return da - db_;
    });

    res.json({
      success: true,
      data:    timeline,
      message: "Timeline aset berhasil diambil"
    });
  } catch (error) {
    console.error("[ASSET TIMELINE ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil timeline aset",
      errors:  { detail: error.message }
    });
  }
};

module.exports = {
  getInventoryAssets,
  getInventoryAsset,
  updateAssetLabel,
  getAssetTimeline
};
