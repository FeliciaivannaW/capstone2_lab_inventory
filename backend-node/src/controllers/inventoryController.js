const db = require("../config/database");
const path = require("path");
const fs = require("fs");

/**
 * Get all inventory assets with optional filters
 * Accessible to: staf_administrasi
 */
const getInventoryAssets = async (req, res, next) => {
  try {
    const { search, status, condition, label_status } = req.query;

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
        ia.created_at,
        ia.updated_at,
        ic.name AS item_name,
        ic.type AS item_type,
        ic.unit AS item_unit,
        icat.name AS category_name,
        r.name AS room_name,
        r.code AS room_code
      FROM inventory_assets AS ia
      JOIN item_catalogs AS ic ON ia.item_catalog_id = ic.id
      LEFT JOIN item_categories AS icat ON ic.category_id = icat.id
      LEFT JOIN rooms AS r ON ia.room_id = r.id
      ${whereClause}
      ORDER BY ia.created_at DESC
    `, params);

    res.json({
      status: "success",
      data: assets
    });
  } catch (error) {
    console.error("[INVENTORY ASSETS ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data inventaris",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Get single inventory asset detail
 */
const getInventoryAsset = async (req, res, next) => {
  try {
    const { id } = req.params;

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
        ia.created_at,
        ia.updated_at,
        ia.procurement_item_id,
        ia.receipt_id,
        ic.name AS item_name,
        ic.type AS item_type,
        ic.unit AS item_unit,
        icat.name AS category_name,
        r.name AS room_name,
        r.code AS room_code
      FROM inventory_assets AS ia
      JOIN item_catalogs AS ic ON ia.item_catalog_id = ic.id
      LEFT JOIN item_categories AS icat ON ic.category_id = icat.id
      LEFT JOIN rooms AS r ON ia.room_id = r.id
      WHERE ia.id = ?
    `, [id]);

    if (rows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Aset inventaris tidak ditemukan"
      });
    }

    res.json({
      status: "success",
      data: rows[0]
    });
  } catch (error) {
    console.error("[INVENTORY ASSET DETAIL ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil detail inventaris",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Update label number and QR/barcode photo for inventory asset
 * Accessible to: staf_administrasi
 */
const updateAssetLabel = async (req, res, next) => {
  try {
    const { id } = req.params;
    const { label_number } = req.body;

    if (!label_number) {
      return res.status(400).json({
        status: "error",
        message: "Nomor label harus diisi"
      });
    }

    // Check asset exists
    const [assetRows] = await db.query(
      `SELECT id, label_number FROM inventory_assets WHERE id = ?`,
      [id]
    );

    if (assetRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Aset inventaris tidak ditemukan"
      });
    }

    // Check label_number uniqueness
    const [duplicateCheck] = await db.query(
      `SELECT id FROM inventory_assets WHERE label_number = ? AND id != ?`,
      [label_number, id]
    );

    if (duplicateCheck.length > 0) {
      return res.status(400).json({
        status: "error",
        message: `Nomor label '${label_number}' sudah digunakan oleh aset lain`
      });
    }

    // Handle photo upload
    let photoUrl = null;
    if (req.file) {
      // Ensure uploads directory exists
      const uploadsDir = path.join(__dirname, '../../uploads/qr');
      if (!fs.existsSync(uploadsDir)) {
        fs.mkdirSync(uploadsDir, { recursive: true });
      }

      photoUrl = `/uploads/qr/${req.file.filename}`;
    }

    // Build update query
    let updateFields = ["label_number = ?"];
    let updateParams = [label_number];

    if (photoUrl) {
      updateFields.push("qr_code = ?");
      updateParams.push(photoUrl);
      updateFields.push("photo_url = ?");
      updateParams.push(photoUrl);
    }

    updateParams.push(id);

    await db.query(
      `UPDATE inventory_assets SET ${updateFields.join(", ")} WHERE id = ?`,
      updateParams
    );

    res.json({
      status: "success",
      message: "Label dan foto berhasil diperbarui",
      data: {
        id,
        label_number,
        photo_url: photoUrl
      }
    });
  } catch (error) {
    console.error("[INVENTORY LABEL UPDATE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal memperbarui label inventaris",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Get asset timeline (lifecycle tracking)
 * Combines procurement, receipt, condition logs, maintenance, disposal
 */
const getAssetTimeline = async (req, res, next) => {
  try {
    const { id } = req.params;

    // Check asset exists
    const [assetRows] = await db.query(
      `SELECT id, asset_code, procurement_item_id, receipt_id FROM inventory_assets WHERE id = ?`,
      [id]
    );

    if (assetRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const asset = assetRows[0];
    let timeline = [];

    // 1. Procurement info
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
        const proc = procRows[0];
        timeline.push({
          type: 'procurement',
          title: 'Pengadaan',
          description: `Diajukan dalam draf "${proc.draft_title}" (${proc.budget_year})`,
          detail: `${proc.item_name} - Rp ${proc.estimated_price} x ${proc.quantity}`,
          date: proc.finalized_at,
          user: proc.created_by_name,
          status: proc.review_status
        });
      }
    }

    // 2. Goods receipt info
    if (asset.receipt_id) {
      const [receiptRows] = await db.query(`
        SELECT gr.received_date, gr.quantity_received, gr.note, u.name AS received_by_name
        FROM goods_receipts AS gr
        JOIN users AS u ON gr.received_by = u.id
        WHERE gr.id = ?
      `, [asset.receipt_id]);

      if (receiptRows.length > 0) {
        const receipt = receiptRows[0];
        timeline.push({
          type: 'receipt',
          title: 'Penerimaan Barang',
          description: `Diterima ${receipt.quantity_received} unit`,
          detail: receipt.note,
          date: receipt.received_date,
          user: receipt.received_by_name,
          status: 'received'
        });
      }
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
        type: 'condition_change',
        title: 'Perubahan Kondisi',
        description: `${log.old_condition || '-'} → ${log.new_condition}`,
        detail: log.note,
        date: log.updated_at,
        user: log.updated_by_name,
        status: log.new_condition
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
        type: 'maintenance',
        title: 'Maintenance',
        description: log.issue_description || 'Pemeliharaan rutin',
        detail: log.action_taken,
        date: log.maintenance_date,
        user: log.performed_by_name,
        status: log.status,
        cost: log.cost
      });
    });

    // 5. Disposal info
    const [disposalRows] = await db.query(`
      SELECT ad.disposal_date, ad.reason, ad.disposal_note, u.name AS disposed_by_name
      FROM asset_disposals AS ad
      JOIN users AS u ON ad.disposed_by = u.id
      WHERE ad.inventory_asset_id = ?
    `, [id]);

    disposalRows.forEach(row => {
      timeline.push({
        type: 'disposal',
        title: 'Penghapusan',
        description: row.reason,
        detail: row.disposal_note,
        date: row.disposal_date,
        user: row.disposed_by_name,
        status: 'disposed'
      });
    });

    // Sort timeline by date
    timeline.sort((a, b) => {
      const dateA = a.date ? new Date(a.date) : new Date(0);
      const dateB = b.date ? new Date(b.date) : new Date(0);
      return dateA - dateB;
    });

    res.json({
      status: "success",
      data: timeline
    });
  } catch (error) {
    console.error("[ASSET TIMELINE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil timeline aset",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  getInventoryAssets,
  getInventoryAsset,
  updateAssetLabel,
  getAssetTimeline
};
