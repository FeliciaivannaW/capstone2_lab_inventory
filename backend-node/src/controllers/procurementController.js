const db = require("../config/database");

/**
 * Get all procurement drafts for the user's laboratory
 * Accessible to: kepala_laboratorium, ketua_program_studi, staf_administrasi
 */
const getProcurementDrafts = async (req, res, next) => {
  try {
    const [drafts] = await db.query(`
      SELECT
        pd.id,
        pd.lab_id,
        pd.title,
        pd.budget_year,
        pd.status,
        pd.is_locked,
        pd.notes,
        pd.created_at,
        pd.submitted_at,
        pd.finalized_at,
        l.name AS lab_name,
        l.code AS lab_code,
        uc.name AS created_by_name,
        uf.name AS finalized_by_name,
        COUNT(pi.id) AS item_count,
        SUM(CASE WHEN pi.review_status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN pi.review_status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
        SUM(CASE WHEN pi.review_status = 'pending' THEN 1 ELSE 0 END) AS pending_count
      FROM procurement_drafts AS pd
      JOIN laboratories AS l ON pd.lab_id = l.id
      JOIN users AS uc ON pd.created_by = uc.id
      LEFT JOIN users AS uf ON pd.finalized_by = uf.id
      LEFT JOIN procurement_items AS pi ON pd.id = pi.draft_id
      GROUP BY pd.id
      ORDER BY pd.created_at DESC
    `);

    res.json({
      status: "success",
      data: drafts
    });
  } catch (error) {
    console.error("[PROCUREMENT DRAFTS ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Get single procurement draft with all items
 * Accessible to: kepala_laboratorium (creator), ketua_program_studi, staf_administrasi
 */
const getProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;

    // Get draft details
    const [draftRows] = await db.query(`
      SELECT
        pd.id,
        pd.lab_id,
        pd.title,
        pd.budget_year,
        pd.status,
        pd.is_locked,
        pd.notes,
        pd.created_at,
        pd.submitted_at,
        pd.finalized_at,
        l.name AS lab_name,
        l.code AS lab_code,
        uc.name AS created_by_name,
        uc.id AS created_by_id,
        uf.name AS finalized_by_name,
        uf.id AS finalized_by_id
      FROM procurement_drafts AS pd
      JOIN laboratories AS l ON pd.lab_id = l.id
      JOIN users AS uc ON pd.created_by = uc.id
      LEFT JOIN users AS uf ON pd.finalized_by = uf.id
      WHERE pd.id = ?
    `, [id]);

    if (draftRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    const draft = draftRows[0];

    // Get items in this draft
    const [items] = await db.query(`
      SELECT
        pi.id,
        pi.item_name,
        pi.item_type,
        pi.quantity,
        pi.estimated_price,
        pi.purchase_link,
        pi.review_status,
        pi.review_note,
        pi.reviewed_at,
        ur.name AS reviewed_by_name,
        ia.asset_code AS replacement_asset_code,
        ic.name AS catalog_name
      FROM procurement_items AS pi
      LEFT JOIN users AS ur ON pi.reviewed_by = ur.id
      LEFT JOIN inventory_assets AS ia ON pi.replacement_asset_id = ia.id
      LEFT JOIN item_catalogs AS ic ON pi.item_catalog_id = ic.id
      WHERE pi.draft_id = ?
      ORDER BY pi.created_at ASC
    `, [id]);

    draft.items = items;

    res.json({
      status: "success",
      data: draft
    });
  } catch (error) {
    console.error("[PROCUREMENT DRAFT DETAIL ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil detail draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Review a single procurement item (approve or reject)
 * Accessible to: ketua_program_studi only
 * Draft must not be locked
 */
const reviewProcurementItem = async (req, res, next) => {
  try {
    const { draftId, itemId } = req.params;
    const { review_status, review_note } = req.body;
    const userId = req.user?.id;

    // Validate input
    if (!['approved', 'rejected'].includes(review_status)) {
      return res.status(400).json({
        status: "error",
        message: "Status review harus 'approved' atau 'rejected'"
      });
    }

    // Check draft exists and not locked
    const [draftRows] = await db.query(
      `SELECT id, status, is_locked FROM procurement_drafts WHERE id = ?`,
      [draftId]
    );

    if (draftRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    const draft = draftRows[0];

    if (draft.is_locked || draft.status === 'finalized') {
      return res.status(403).json({
        status: "error",
        message: "Draf sudah terkunci, tidak bisa diubah"
      });
    }

    // Check item exists in draft
    const [itemRows] = await db.query(
      `SELECT id FROM procurement_items WHERE id = ? AND draft_id = ?`,
      [itemId, draftId]
    );

    if (itemRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Item pengadaan tidak ditemukan"
      });
    }

    // Update item review
    const reviewedAt = new Date();
    await db.query(
      `UPDATE procurement_items 
       SET review_status = ?, review_note = ?, reviewed_by = ?, reviewed_at = ?
       WHERE id = ?`,
      [review_status, review_note || null, userId, reviewedAt, itemId]
    );

    res.json({
      status: "success",
      message: `Item berhasil di-${review_status === 'approved' ? 'setujui' : 'tolak'}`,
      data: {
        item_id: itemId,
        review_status,
        reviewed_at: reviewedAt
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT ITEM REVIEW ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal melakukan review item pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

/**
 * Finalize a procurement draft (lock it)
 * Accessible to: ketua_program_studi only
 * Draft must be in 'submitted' status
 */
const finalizeProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const userId = req.user?.id;

    // Check draft exists and can be finalized
    const [draftRows] = await db.query(
      `SELECT id, status, is_locked FROM procurement_drafts WHERE id = ?`,
      [id]
    );

    if (draftRows.length === 0) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    const draft = draftRows[0];

    if (draft.is_locked || draft.status === 'finalized') {
      return res.status(403).json({
        status: "error",
        message: "Draf sudah terkunci atau sudah difinalisasi"
      });
    }

    // Update draft to finalized
    const finalizedAt = new Date();
    await db.query(
      `UPDATE procurement_drafts 
       SET status = 'finalized', is_locked = true, finalized_by = ?, finalized_at = ?
       WHERE id = ?`,
      [userId, finalizedAt, id]
    );

    res.json({
      status: "success",
      message: "Draf pengadaan berhasil difinalisasi dan terkunci",
      data: {
        draft_id: id,
        status: 'finalized',
        is_locked: true,
        finalized_at: finalizedAt
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT FINALIZE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal menfinalisasi draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  getProcurementDrafts,
  getProcurementDraft,
  reviewProcurementItem,
  finalizeProcurementDraft
};
