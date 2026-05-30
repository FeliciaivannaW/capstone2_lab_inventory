const db = require("../config/database");

const InventoryModel = {
  /**
   * Find inventory assets based on filters
   */
  async findAll({ search, status, condition, label_status, lab_id, roomIds, receipt_id } = {}) {
    let whereConditions = [];
    let params = [];

    if (search) {
      whereConditions.push("(ia.asset_code LIKE ? OR ia.label_number LIKE ? OR COALESCE(ic.name, pi.item_name) LIKE ? OR r.name LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`);
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

    if (lab_id) {
      whereConditions.push("COALESCE(lproc.id, lroom.id) = ?");
      params.push(lab_id);
    }

    if (Array.isArray(roomIds) && roomIds.length > 0) {
      whereConditions.push("ia.room_id IN (?)");
      params.push(roomIds);
    }

    if (receipt_id) {
      whereConditions.push("ia.receipt_id = ?");
      params.push(Number(receipt_id));
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
        COALESCE(ic.name, pi.item_name)    AS item_name,
        COALESCE(ic.type, pi.item_type)    AS item_type,
        ic.unit                             AS item_unit,
        icat.name                           AS category_name,
        r.id   AS room_id,
        r.name AS room_name,
        r.code AS room_code,
        COALESCE(lproc.id,   lroom.id)   AS lab_id,
        COALESCE(lproc.name, lroom.name) AS lab_name,
        COALESCE(lproc.code, lroom.code) AS lab_code,
        pd.id    AS draft_id,
        pd.title AS draft_title,
        gr.received_date AS receipt_date,
        gr.quantity_received
      FROM inventory_assets AS ia
      LEFT JOIN item_catalogs AS ic   ON ia.item_catalog_id = ic.id
      LEFT JOIN item_categories AS icat ON ic.category_id = icat.id
      LEFT JOIN rooms AS r             ON ia.room_id = r.id
      LEFT JOIN laboratories AS lroom  ON r.id = lroom.room_id
      LEFT JOIN procurement_items AS pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories AS lproc  ON pd.lab_id = lproc.id
      LEFT JOIN goods_receipts AS gr   ON ia.receipt_id = gr.id
      ${whereClause}
      ORDER BY ia.receipt_id DESC, ia.id ASC
    `, params);

    return assets;
  },

  /**
   * Find main asset by ID
   */
  async findById(id) {
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

    return rows[0] || null;
  },

  /**
   * Find asset record for checking duplicates (locked for transaction)
   */
  async findByIdForUpdate(id, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(`
      SELECT
        id,
        room_id,
        label_number,
        asset_code,
        status,
        asset_condition
      FROM inventory_assets
      WHERE id = ?
      LIMIT 1
      FOR UPDATE
    `, [id]);
    return rows[0] || null;
  },

  /**
   * Find duplicate asset by label number
   */
  async findByLabelNumberExcludeId(labelNumber, id, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(
      "SELECT id FROM inventory_assets WHERE label_number = ? AND id != ?",
      [labelNumber, id]
    );
    return rows[0] || null;
  },

  /**
   * Get count of assets in a lab and year (for generating codes)
   */
  async countAssetsByLabAndYear(labCode, year, tx = null) {
    const conn = tx || db;
    const [rows] = await conn.query(`
      SELECT COUNT(*) AS cnt
      FROM inventory_assets AS ia
      JOIN procurement_items AS pi ON ia.procurement_item_id = pi.id
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      JOIN laboratories AS l ON pd.lab_id = l.id
      WHERE l.code = ? AND YEAR(ia.received_date) = ?
    `, [labCode, year]);
    return rows[0]?.cnt || 0;
  },

  /**
   * Insert a new inventory asset
   */
  async createAsset({ item_catalog_id, procurement_item_id, receipt_id, asset_code, received_date, status = 'received', asset_condition = 'baik' }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO inventory_assets (
        item_catalog_id, procurement_item_id, receipt_id,
        asset_code, received_date, status, asset_condition,
        replaced_by_asset_id, created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NOW(), NOW())
    `, [item_catalog_id, procurement_item_id, receipt_id, asset_code, received_date, status, asset_condition]);
    return result;
  },

  /**
   * Update asset label and fields dynamically
   */
  async updateLabel(id, { label_number, asset_code, barcode, photo_url, qr_url, serial_number, room_id, status = 'labeled' }, tx = null) {
    const conn = tx || db;
    const updateFields = ["label_number = ?", "status = ?", "updated_at = NOW()"];
    const updateParams = [label_number, status];

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
    }
    if (qr_url) {
      updateFields.push("qr_code = ?");
      updateParams.push(qr_url);
    }
    if (serial_number) {
      updateFields.push("serial_number = ?");
      updateParams.push(serial_number);
    }
    if (room_id) {
      updateFields.push("room_id = ?");
      updateParams.push(Number(room_id));
    }

    updateParams.push(id);

    const [result] = await conn.query(
      `UPDATE inventory_assets SET ${updateFields.join(", ")} WHERE id = ?`,
      updateParams
    );
    return result;
  },

  /**
   * Create asset condition log
   */
  async createConditionLog({ inventory_asset_id, updated_by, old_condition, new_condition, note }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO asset_condition_logs (inventory_asset_id, updated_by, old_condition, new_condition, note, updated_at)
      VALUES (?, ?, ?, ?, ?, NOW())
    `, [inventory_asset_id, updated_by, old_condition, new_condition, note]);
    return result;
  },

  /**
   * Get procurement origin details for asset timeline
   */
  async findTimelineProcurement(procurementItemId) {
    const [rows] = await db.query(`
      SELECT
        pi.item_name, pi.estimated_price, pi.quantity, pi.review_status, pi.reviewed_at,
        pd.title AS draft_title, pd.budget_year, pd.finalized_at,
        uc.name AS created_by_name
      FROM procurement_items AS pi
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      JOIN users AS uc ON pd.created_by = uc.id
      WHERE pi.id = ?
    `, [procurementItemId]);
    return rows[0] || null;
  },

  /**
   * Get goods_receipt list for asset timeline
   */
  async findTimelineReceipts(procurementItemId) {
    const [rows] = await db.query(`
      SELECT gr.id, gr.received_date, gr.quantity_received, gr.note, u.name AS received_by_name
      FROM goods_receipts AS gr
      JOIN users AS u ON gr.received_by = u.id
      WHERE gr.procurement_item_id = ?
      ORDER BY gr.received_date ASC
    `, [procurementItemId]);
    return rows;
  },

  /**
   * Get condition logs for asset timeline
   */
  async findTimelineConditionLogs(assetId) {
    const [rows] = await db.query(`
      SELECT acl.old_condition, acl.new_condition, acl.note, acl.updated_at, u.name AS updated_by_name
      FROM asset_condition_logs AS acl
      JOIN users AS u ON acl.updated_by = u.id
      WHERE acl.inventory_asset_id = ?
      ORDER BY acl.updated_at ASC
    `, [assetId]);
    return rows;
  },

  /**
   * Get maintenance logs for asset timeline
   */
  async findTimelineMaintenance(assetId) {
    const [rows] = await db.query(`
      SELECT ml.maintenance_date, ml.issue_description, ml.action_taken,
             ml.condition_before, ml.condition_after, ml.status, ml.cost, ml.notes,
             u.name AS performed_by_name
      FROM maintenance_logs AS ml
      JOIN users AS u ON ml.performed_by = u.id
      WHERE ml.inventory_asset_id = ?
      ORDER BY ml.maintenance_date ASC
    `, [assetId]);
    return rows;
  },

  /**
   * Get disposal logs for asset timeline
   */
  async findTimelineDisposal(assetId) {
    const [rows] = await db.query(`
      SELECT ad.disposal_date, ad.reason, ad.disposal_note, u.name AS disposed_by_name
      FROM asset_disposals AS ad
      JOIN users AS u ON ad.disposed_by = u.id
      WHERE ad.inventory_asset_id = ?
    `, [assetId]);
    return rows;
  },

  async updateCondition(id, { condition, status, note }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE inventory_assets
      SET asset_condition = ?, status = ?, notes = COALESCE(?, notes), updated_at = NOW()
      WHERE id = ?
    `, [condition, status, note || null, id]);

    return result;
  },

  async findConditionHistory({ search, condition, roomIds }) {
    const whereConditions = [];
    const params = [];

    if (search) {
      whereConditions.push("(ia.asset_code LIKE ? OR ia.label_number LIKE ? OR ic.name LIKE ? OR acl.note LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`);
    }

    if (condition) {
      whereConditions.push("acl.new_condition = ?");
      params.push(condition);
    }

    if (Array.isArray(roomIds) && roomIds.length > 0) {
      whereConditions.push("ia.room_id IN (?)");
      params.push(roomIds);
    }

    const whereClause = whereConditions.length ? `WHERE ${whereConditions.join(" AND ")}` : "";

    const [rows] = await db.query(`
      SELECT
        acl.id,
        acl.inventory_asset_id,
        acl.old_condition,
        acl.new_condition,
        acl.note,
        acl.updated_at,
        ia.asset_code,
        ia.label_number,
        ia.status,
        ic.name AS item_name,
        r.code AS room_code,
        r.name AS room_name,
        u.name AS updated_by_name
      FROM asset_condition_logs acl
      JOIN inventory_assets ia ON acl.inventory_asset_id = ia.id
      JOIN item_catalogs ic ON ia.item_catalog_id = ic.id
      LEFT JOIN rooms r ON ia.room_id = r.id
      LEFT JOIN users u ON acl.updated_by = u.id
      ${whereClause}
      ORDER BY acl.updated_at DESC, acl.id DESC
    `, params);

    return rows;
  }

};

module.exports = InventoryModel;
