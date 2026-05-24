const db = require("../config/database");

/**
 * GET /goods-receipts/pending
 * Returns all approved procurement items that are NOT yet fully received,
 * grouped by draft. Used by Staf Admin to see what still needs to be received.
 */
const getPendingItems = async (req, res, next) => {
  try {
    // Fetch all approved items from finalized drafts with their total received qty
    const [rows] = await db.query(`
      SELECT
        pi.id                         AS item_id,
        pi.item_name,
        pi.item_type,
        pi.quantity                   AS quantity_ordered,
        pi.estimated_price,
        pi.review_status,
        pi.replacement_asset_id,
        pd.id                         AS draft_id,
        pd.title                      AS draft_title,
        pd.budget_year,
        pd.finalized_at,
        l.id                          AS lab_id,
        l.name                        AS lab_name,
        l.code                        AS lab_code,
        COALESCE(SUM(gr.quantity_received), 0)  AS quantity_received_so_far
      FROM procurement_items AS pi
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      JOIN laboratories AS l ON pd.lab_id = l.id
      LEFT JOIN goods_receipts AS gr ON gr.procurement_item_id = pi.id
      WHERE pi.review_status = 'approved'
        AND pd.status = 'finalized'
      GROUP BY pi.id, pd.id, l.id
      HAVING quantity_received_so_far < pi.quantity
      ORDER BY pd.finalized_at ASC, pi.item_name ASC
    `);

    // Group by draft
    const draftsMap = {};
    rows.forEach(row => {
      const draftId = row.draft_id;
      if (!draftsMap[draftId]) {
        draftsMap[draftId] = {
          draft_id:    draftId,
          draft_title: row.draft_title,
          budget_year: row.budget_year,
          finalized_at: row.finalized_at,
          lab_id:      row.lab_id,
          lab_name:    row.lab_name,
          lab_code:    row.lab_code,
          items:       []
        };
      }
      draftsMap[draftId].items.push({
        item_id:                  row.item_id,
        item_name:                row.item_name,
        item_type:                row.item_type,
        quantity_ordered:         row.quantity_ordered,
        quantity_received_so_far: row.quantity_received_so_far,
        remaining_qty:            row.quantity_ordered - row.quantity_received_so_far,
        estimated_price:          row.estimated_price,
        replacement_asset_id:     row.replacement_asset_id
      });
    });

    res.json({
      success: true,
      data:    Object.values(draftsMap),
      message: "Data pending items berhasil diambil"
    });
  } catch (error) {
    console.error("[GOODS RECEIPTS PENDING ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil data pending items",
      errors:  { detail: error.message }
    });
  }
};

/**
 * GET /goods-receipts
 * Optional query: ?procurement_item_id=:id
 * Returns all receipt history for a specific procurement item,
 * OR all receipts if no filter is provided.
 */
const getReceiptsByItem = async (req, res, next) => {
  try {
    const { procurement_item_id } = req.query;

    let whereClause = "";
    let params = [];

    if (procurement_item_id) {
      whereClause = "WHERE gr.procurement_item_id = ?";
      params.push(procurement_item_id);
    }

    const [receipts] = await db.query(`
      SELECT
        gr.id,
        gr.procurement_item_id,
        gr.received_date,
        gr.quantity_received,
        gr.note,
        gr.created_at,
        pi.item_name,
        pi.item_type,
        pi.quantity AS ordered_quantity,
        u.name AS received_by_name,
        u.id   AS received_by_id
      FROM goods_receipts AS gr
      JOIN procurement_items AS pi ON gr.procurement_item_id = pi.id
      JOIN users AS u ON gr.received_by = u.id
      ${whereClause}
      ORDER BY gr.received_date DESC, gr.created_at DESC
    `, params);

    res.json({
      success: true,
      data:    receipts,
      message: "Riwayat penerimaan berhasil diambil"
    });
  } catch (error) {
    console.error("[GOODS RECEIPTS BY ITEM ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil riwayat penerimaan",
      errors:  { detail: error.message }
    });
  }
};

/**
 * GET /goods-receipts/by-draft/:draftId
 * Returns all receipts for items in a specific procurement draft.
 * Used by StafAdminController (Laravel) to compute progress per draft.
 */
const getReceiptsByDraft = async (req, res, next) => {
  try {
    const { draftId } = req.params;

    const [receipts] = await db.query(`
      SELECT
        gr.id,
        gr.procurement_item_id,
        gr.received_date,
        gr.quantity_received,
        gr.note,
        gr.created_at,
        pi.item_name,
        pi.item_type,
        pi.quantity AS ordered_quantity,
        u.name AS received_by_name
      FROM goods_receipts AS gr
      JOIN procurement_items AS pi ON gr.procurement_item_id = pi.id
      JOIN users AS u ON gr.received_by = u.id
      WHERE pi.draft_id = ?
      ORDER BY gr.received_date DESC
    `, [draftId]);

    res.json({
      success: true,
      data:    receipts
    });
  } catch (error) {
    console.error("[GOODS RECEIPTS BY DRAFT ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil data penerimaan barang",
      errors:  { detail: error.message }
    });
  }
};

/**
 * POST /goods-receipts
 * Records the receipt of a procurement item (supports partial delivery).
 *
 * After insert:
 *   - item_type = 'inventory' → buat N records di inventory_assets (1 per unit)
 *     asset_code format: INV-{KODE_LAB}-{TAHUN}-{SEQ 3 digit zero-padded}
 *   - item_type = 'bhp' → UPSERT bhp_stocks + insert bhp_stock_movements
 *
 * Validations:
 *   - quantity_received > 0
 *   - quantity_received <= remaining_qty
 *   - received_date <= today (no future dates)
 *   - item must have review_status = 'approved'
 *   - draft must be 'finalized'
 */
const createGoodsReceipt = async (req, res, next) => {
  // Use a DB connection for transaction support
  const connection = await db.getConnection();
  try {
    await connection.beginTransaction();

    const { procurement_item_id, received_date, quantity_received, note } = req.body;
    const userId = req.user?.id;

    // ── Input validation ──────────────────────────────────────────
    if (!procurement_item_id || !received_date || !quantity_received) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: "Field procurement_item_id, received_date, dan quantity_received harus diisi",
        errors:  {}
      });
    }

    const qty = parseInt(quantity_received, 10);
    if (isNaN(qty) || qty <= 0) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: "Jumlah yang diterima harus lebih dari 0",
        errors:  { quantity_received: "Harus > 0" }
      });
    }

    // Validate: received_date must not be in the future
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    const receiptDate = new Date(received_date);
    if (receiptDate > today) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: "Tanggal terima tidak boleh tanggal masa depan",
        errors:  { received_date: "Tidak boleh tanggal masa depan" }
      });
    }

    // ── Fetch procurement item + draft + lab info ─────────────────
    const [itemRows] = await connection.query(`
      SELECT
        pi.id,
        pi.item_name,
        pi.item_type,
        pi.item_catalog_id,
        pi.quantity,
        pi.review_status,
        pi.replacement_asset_id,
        pi.draft_id,
        pd.status       AS draft_status,
        pd.lab_id,
        pd.budget_year,
        l.code          AS lab_code
      FROM procurement_items AS pi
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      JOIN laboratories AS l ON pd.lab_id = l.id
      WHERE pi.id = ?
    `, [procurement_item_id]);

    if (itemRows.length === 0) {
      await connection.rollback();
      connection.release();
      return res.status(404).json({
        success: false,
        message: "Item pengadaan tidak ditemukan"
      });
    }

    const item = itemRows[0];

    // Validate: item must be approved
    if (item.review_status !== 'approved') {
      await connection.rollback();
      connection.release();
      return res.status(403).json({
        success: false,
        message: "Hanya item yang disetujui Kaprodi yang bisa diinput tanggal terima"
      });
    }

    // Validate: draft must be finalized
    if (item.draft_status !== 'finalized') {
      await connection.rollback();
      connection.release();
      return res.status(403).json({
        success: false,
        message: "Draf belum difinalisasi oleh Kaprodi"
      });
    }

    // ── Check remaining qty ───────────────────────────────────────
    const [existingRows] = await connection.query(`
      SELECT COALESCE(SUM(quantity_received), 0) AS total_received
      FROM goods_receipts
      WHERE procurement_item_id = ?
    `, [procurement_item_id]);

    const totalReceived = parseInt(existingRows[0].total_received, 10);
    const remaining     = item.quantity - totalReceived;

    if (qty > remaining) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: `Jumlah melebihi sisa pesanan. Sisa: ${remaining}, dipesan total: ${item.quantity}, sudah diterima: ${totalReceived}`,
        errors:  { quantity_received: `Maks ${remaining}` }
      });
    }

    // ── Insert goods_receipt ──────────────────────────────────────
    const [receiptResult] = await connection.query(`
      INSERT INTO goods_receipts (procurement_item_id, received_by, received_date, quantity_received, note)
      VALUES (?, ?, ?, ?, ?)
    `, [procurement_item_id, userId, received_date, qty, note || null]);

    const receiptId = receiptResult.insertId;

    // ── After-insert logic based on item_type ─────────────────────
    const createdAssets = [];

    if (item.item_type === 'inventory') {
      // Create N inventory_asset records (one per unit received)
      // asset_code format: INV-{KODE_LAB}-{TAHUN}-{SEQ 3-digit, global per lab+year}
      const labCode   = (item.lab_code || 'LAB').toUpperCase();
      const year      = new Date(received_date).getFullYear();

      // Find current max seq for this lab+year to generate next seq
      const [seqRows] = await connection.query(`
        SELECT COUNT(*) AS cnt
        FROM inventory_assets AS ia
        JOIN procurement_items AS pi ON ia.procurement_item_id = pi.id
        JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
        JOIN laboratories AS l ON pd.lab_id = l.id
        WHERE l.code = ? AND YEAR(ia.received_date) = ?
      `, [item.lab_code, year]);

      let nextSeq = parseInt(seqRows[0].cnt, 10) + 1;

      for (let i = 0; i < qty; i++) {
        const seqStr   = String(nextSeq).padStart(3, '0');
        const assetCode = `INV-${labCode}-${year}-${seqStr}`;

        const [assetResult] = await connection.query(`
          INSERT INTO inventory_assets (
            item_catalog_id, procurement_item_id, receipt_id,
            asset_code, received_date, status, asset_condition,
            replaced_by_asset_id, created_at, updated_at
          ) VALUES (?, ?, ?, ?, ?, 'received', 'baik', NULL, NOW(), NOW())
        `, [
          item.item_catalog_id,
          procurement_item_id,
          receiptId,
          assetCode,
          received_date
        ]);

        createdAssets.push({
          id:         assetResult.insertId,
          asset_code: assetCode
        });

        nextSeq++;
      }

    } else if (item.item_type === 'bhp') {
      // UPSERT bhp_stocks — increment current_stock
      // bhp_stocks is unique per (lab_id, item_catalog_id)

      // Fetch unit from item_catalogs
      const [catalogRows] = await connection.query(
        `SELECT unit FROM item_catalogs WHERE id = ?`,
        [item.item_catalog_id]
      );
      const unit = catalogRows[0]?.unit || 'unit';

      const [existingStock] = await connection.query(`
        SELECT id, current_stock FROM bhp_stocks
        WHERE lab_id = ? AND item_catalog_id = ?
      `, [item.lab_id, item.item_catalog_id]);

      let stockId;

      if (existingStock.length > 0) {
        // UPDATE existing stock
        stockId = existingStock[0].id;
        await connection.query(`
          UPDATE bhp_stocks
          SET current_stock = current_stock + ?, updated_at = NOW()
          WHERE id = ?
        `, [qty, stockId]);
      } else {
        // INSERT new stock record
        const [stockInsert] = await connection.query(`
          INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit, updated_at)
          VALUES (?, ?, ?, 0, ?, NOW())
        `, [item.lab_id, item.item_catalog_id, qty, unit]);
        stockId = stockInsert.insertId;
      }

      // Insert bhp_stock_movements for audit trail
      await connection.query(`
        INSERT INTO bhp_stock_movements (
          stock_id, procurement_item_id, receipt_id, performed_by,
          movement_type, quantity, movement_date, note, created_at
        ) VALUES (?, ?, ?, ?, 'in', ?, NOW(), ?, NOW())
      `, [
        stockId,
        procurement_item_id,
        receiptId,
        userId,
        qty,
        note || `Penerimaan barang dari draf pengadaan`
      ]);
    }

    await connection.commit();
    connection.release();

    res.status(201).json({
      success: true,
      message: "Penerimaan barang berhasil dicatat",
      data: {
        receipt_id:           receiptId,
        procurement_item_id:  parseInt(procurement_item_id, 10),
        received_date,
        quantity_received:    qty,
        total_received:       totalReceived + qty,
        ordered_quantity:     item.quantity,
        remaining_qty:        remaining - qty,
        item_type:            item.item_type,
        // Only populated for inventory type
        created_assets:       item.item_type === 'inventory' ? createdAssets : []
      }
    });

  } catch (error) {
    try { await connection.rollback(); } catch (_) {}
    connection.release();
    console.error("[GOODS RECEIPT CREATE ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mencatat penerimaan barang",
      errors:  { detail: error.message }
    });
  }
};

module.exports = {
  getPendingItems,
  getReceiptsByItem,
  getReceiptsByDraft,
  createGoodsReceipt
};
