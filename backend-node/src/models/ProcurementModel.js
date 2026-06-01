const db = require("../config/database");

const ProcurementModel = {
  /**
   * Find procurement drafts based on filters
   */
  async findDrafts({ status, budget_year, search, lab_id }) {
    let whereConditions = [];
    let params = [];

    if (status) {
      whereConditions.push("pd.status = ?");
      params.push(status);
    }

    if (budget_year) {
      whereConditions.push("pd.budget_year = ?");
      params.push(budget_year);
    }

    if (search) {
      whereConditions.push("(pd.title LIKE ? OR l.name LIKE ?)");
      params.push(`%${search}%`, `%${search}%`);
    }

    if (lab_id) {
      whereConditions.push("pd.lab_id = ?");
      params.push(lab_id);
    }

    const whereClause = whereConditions.length > 0
      ? "WHERE " + whereConditions.join(" AND ")
      : "";

    const [rows] = await db.query(`
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
        SUM(CASE WHEN pi.review_status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(
          CASE WHEN pi.review_status = 'approved' AND (
            SELECT COALESCE(SUM(gr2.quantity_received), 0)
            FROM goods_receipts gr2
            WHERE gr2.procurement_item_id = pi.id
          ) >= pi.quantity THEN 1 ELSE 0 END
        ) AS received_items,
        SUM(
          CASE WHEN pi.review_status = 'approved' AND (
            SELECT COALESCE(SUM(gr3.quantity_received), 0)
            FROM goods_receipts gr3
            WHERE gr3.procurement_item_id = pi.id
          ) < pi.quantity THEN 1 ELSE 0 END
        ) AS pending_items
      FROM procurement_drafts AS pd
      JOIN laboratories AS l ON pd.lab_id = l.id
      JOIN users AS uc ON pd.created_by = uc.id
      LEFT JOIN users AS uf ON pd.finalized_by = uf.id
      LEFT JOIN procurement_items AS pi ON pd.id = pi.draft_id
      ${whereClause}
      GROUP BY pd.id
      ORDER BY pd.created_at DESC
    `, params);

    return rows;
  },

  /**
   * Find single draft details by ID
   */
  async findDraftById(id) {
    const [rows] = await db.query(`
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

    return rows[0] || null;
  },

  /**
   * Find items for a specific draft
   */
  async findItemsByDraftId(draftId) {
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
    `, [draftId]);

    return items;
  },

  /**
   * Find draft for lock/update validation
   */
  async findDraftByIdForLock(id, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(
      "SELECT id, status, is_locked, created_by FROM procurement_drafts WHERE id = ?",
      [id]
    );
    return rows[0] || null;
  },

  /**
   * Find item by ID and draft ID
   */
  async findItemByIdAndDraftId(itemId, draftId, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(
      "SELECT id, review_status FROM procurement_items WHERE id = ? AND draft_id = ?",
      [itemId, draftId]
    );
    return rows[0] || null;
  },

  /**
   * Update procurement item review status
   */
  async updateItemReview(itemId, { reviewStatus, reviewNote, reviewedBy, reviewedAt }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE procurement_items 
      SET review_status = ?, review_note = ?, reviewed_by = ?, reviewed_at = ?
      WHERE id = ?
    `, [reviewStatus, reviewNote || null, reviewedBy, reviewedAt, itemId]);
    return result;
  },

  /**
   * Finalize a procurement draft
   */
  async finalizeDraft(id, { status = 'finalized', isLocked = true, finalizedBy, finalizedAt }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE procurement_drafts 
      SET status = ?, is_locked = ?, finalized_by = ?, finalized_at = ?
      WHERE id = ?
    `, [status, isLocked, finalizedBy, finalizedAt, id]);
    return result;
  },

  /**
   * Create procurement draft
   */
  async createDraft({ labId, createdBy, title, budgetYear, notes, status = 'draft', createdAt }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO procurement_drafts (lab_id, created_by, title, budget_year, notes, status, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `, [labId, createdBy, title, budgetYear, notes || null, status, createdAt]);
    return result;
  },

  /**
   * Update procurement draft
   */
  async updateDraft(id, { title, labId, budgetYear, notes }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE procurement_drafts SET title = ?, lab_id = ?, budget_year = ?, notes = ?
      WHERE id = ?
    `, [title, labId, budgetYear, notes || null, id]);
    return result;
  },

  /**
   * Count items in draft
   */
  async countItemsInDraft(draftId, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(
      "SELECT COUNT(*) as count FROM procurement_items WHERE draft_id = ?",
      [draftId]
    );
    return rows[0]?.count || 0;
  },

  async deleteDraft(id, tx = null) {
    const conn = tx || db;
    await conn.query("DELETE FROM procurement_items WHERE draft_id = ?", [id]);
    const [result] = await conn.query("DELETE FROM procurement_drafts WHERE id = ?", [id]);
    return result;
  },

  /**
   * Create a new procurement item
   */
  async createItem({ draftId, itemName, itemType, quantity, estimatedPrice, purchaseLink, replacementAssetId, createdAt }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO procurement_items 
      (draft_id, item_name, item_type, quantity, estimated_price, purchase_link, replacement_asset_id, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `, [draftId, itemName, itemType, quantity, estimatedPrice, purchaseLink || null, replacementAssetId || null, createdAt]);
    return result;
  },

  /**
   * Delete item
   */
  async deleteItem(itemId, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query("DELETE FROM procurement_items WHERE id = ?", [itemId]);
    return result;
  },

  /**
   * Bulk sync draft items (replace all)
   */
  async syncItems(draftId, items, tx = null) {
    const conn = tx || db;
    await conn.query("DELETE FROM procurement_items WHERE draft_id = ?", [draftId]);

    if (items && items.length > 0) {
      const values = items.map(item => [
        draftId,
        item.item_name,
        item.item_type,
        item.quantity,
        item.estimated_price,
        item.purchase_link || null,
        item.replacement_asset_id || null,
        item.review_status || 'pending',
        item.review_note || null,
        item.created_at ? new Date(item.created_at) : new Date(),
        item.reviewed_at ? new Date(item.reviewed_at) : null
      ]);

      await conn.query(`
        INSERT INTO procurement_items 
        (draft_id, item_name, item_type, quantity, estimated_price, purchase_link, replacement_asset_id, review_status, review_note, created_at, reviewed_at)
        VALUES ?
      `, [values]);
    }
  },

  /**
   * Submit draft
   */
  async submitDraft(id, { status = 'submitted', submittedAt }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(
      "UPDATE procurement_drafts SET status = ?, submitted_at = ? WHERE id = ?",
      [status, submittedAt, id]
    );
    return result;
  },

  /**
   * Update procurement item details dynamically
   */
  async updateItem(itemId, fields, params, tx = null) {
    const conn = tx || db;
    const updateFields = Object.keys(fields).map(field => `${field} = ?`);
    const updateParams = Object.values(fields);
    updateParams.push(itemId);

    const [result] = await conn.query(
      `UPDATE procurement_items SET ${updateFields.join(', ')} WHERE id = ?`,
      updateParams
    );
    return result;
  },

  /**
   * Find item details specifically for receiving goods
   */
  async findItemDetailsForReceipt(itemId, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(`
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
    `, [itemId]);
    return rows[0] || null;
  }
};

module.exports = ProcurementModel;
