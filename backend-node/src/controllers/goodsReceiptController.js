const db = require("../config/database");

/**
 * Get goods receipts by draft ID
 * Returns all receipts for items in a specific procurement draft
 * Accessible to: staf_administrasi
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
        pi.quantity AS ordered_quantity,
        u.name AS received_by_name
      FROM goods_receipts AS gr
      JOIN procurement_items AS pi ON gr.procurement_item_id = pi.id
      JOIN users AS u ON gr.received_by = u.id
      WHERE pi.draft_id = ?
      ORDER BY gr.received_date DESC
    `, [draftId]);

    res.json({
      status: "success",
      data: receipts
    });
  } catch (error) {
    console.error("[GOODS RECEIPTS BY DRAFT ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data penerimaan barang",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Create a new goods receipt
 * Records the receipt of a specific procurement item
 * Accessible to: staf_administrasi
 */
const createGoodsReceipt = async (req, res, next) => {
  try {
    const { procurement_item_id, received_date, quantity_received, note } = req.body;
    const userId = req.user?.id;

    // Validate input
    if (!procurement_item_id || !received_date || !quantity_received) {
      return res.status(400).json({
        status: "error",
        message: "Field procurement_item_id, received_date, dan quantity_received harus diisi"
      });
    }

    // Check procurement item exists and is approved
    const [itemRows] = await db.query(`
      SELECT
        pi.id,
        pi.item_name,
        pi.quantity,
        pi.review_status,
        pi.draft_id,
        pd.status AS draft_status
      FROM procurement_items AS pi
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      WHERE pi.id = ?
    `, [procurement_item_id]);

    if (itemRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Item pengadaan tidak ditemukan"
      });
    }

    const item = itemRows[0];

    // Validate: only approved items from finalized drafts
    if (item.review_status !== 'approved') {
      return res.status(403).json({
        status: "error",
        message: "Hanya item yang disetujui Kaprodi yang bisa diinput tanggal terima"
      });
    }

    if (item.draft_status !== 'finalized') {
      return res.status(403).json({
        status: "error",
        message: "Draf belum difinalisasi"
      });
    }

    // Check total received quantity doesn't exceed ordered quantity
    const [existingReceipts] = await db.query(`
      SELECT COALESCE(SUM(quantity_received), 0) AS total_received
      FROM goods_receipts
      WHERE procurement_item_id = ?
    `, [procurement_item_id]);

    const totalReceived = existingReceipts[0].total_received;
    if (totalReceived + parseInt(quantity_received) > item.quantity) {
      return res.status(400).json({
        status: "error",
        message: `Jumlah penerimaan melebihi pesanan. Sudah diterima: ${totalReceived}, dipesan: ${item.quantity}`
      });
    }

    // Insert goods receipt
    const [result] = await db.query(`
      INSERT INTO goods_receipts (procurement_item_id, received_by, received_date, quantity_received, note)
      VALUES (?, ?, ?, ?, ?)
    `, [procurement_item_id, userId, received_date, quantity_received, note || null]);

    res.json({
      status: "success",
      message: "Penerimaan barang berhasil dicatat",
      data: {
        id: result.insertId,
        procurement_item_id,
        received_date,
        quantity_received,
        total_received: totalReceived + parseInt(quantity_received),
        ordered_quantity: item.quantity
      }
    });
  } catch (error) {
    console.error("[GOODS RECEIPT CREATE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mencatat penerimaan barang",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  getReceiptsByDraft,
  createGoodsReceipt
};
