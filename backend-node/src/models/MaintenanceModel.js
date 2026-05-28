const db = require("../config/database");

const MaintenanceModel = {
  async findLogs({ assetId, status, search, labIds = [] }) {
    const conditions = [];
    const params = [];

    if (labIds.length) {
      conditions.push("COALESCE(lproc.id, lroom.id) IN (?)");
      params.push(labIds);
    }

    if (assetId) {
      conditions.push("ml.inventory_asset_id = ?");
      params.push(Number(assetId));
    }

    if (status) {
      conditions.push("ml.status = ?");
      params.push(status);
    }

    if (search) {
      conditions.push("(ia.asset_code LIKE ? OR ia.label_number LIKE ? OR ic.name LIKE ? OR ml.issue_description LIKE ? OR ml.action_taken LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`);
    }

    const whereClause = conditions.length ? `WHERE ${conditions.join(" AND ")}` : "";

    const [logs] = await db.query(`
      SELECT
        ml.id,
        ml.inventory_asset_id,
        ia.asset_code,
        ia.label_number,
        ic.name AS item_name,
        r.name AS room_name,
        r.code AS room_code,
        COALESCE(lproc.id, lroom.id) AS lab_id,
        COALESCE(lproc.name, lroom.name) AS laboratory_name,
        u.name AS performed_by_name,
        ml.maintenance_date,
        ml.issue_description,
        ml.action_taken,
        ml.condition_before,
        ml.condition_after,
        ml.status,
        ml.cost,
        ml.notes,
        ml.created_at,
        ml.updated_at
      FROM maintenance_logs ml
      JOIN inventory_assets ia ON ml.inventory_asset_id = ia.id
      JOIN item_catalogs ic ON ia.item_catalog_id = ic.id
      LEFT JOIN rooms r ON ia.room_id = r.id
      LEFT JOIN laboratories lroom ON r.id = lroom.room_id
      LEFT JOIN procurement_items pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories lproc ON pd.lab_id = lproc.id
      JOIN users u ON ml.performed_by = u.id
      ${whereClause}
      ORDER BY ml.maintenance_date DESC, ml.id DESC
    `, params);

    return logs;
  },

  async findBhpUsages(maintenanceIds) {
    if (!maintenanceIds || maintenanceIds.length === 0) return [];

    const [usageRows] = await db.query(`
      SELECT
        m.maintenance_id,
        m.stock_id,
        ic.name AS item_name,
        bs.unit,
        m.quantity,
        m.note
      FROM bhp_stock_movements m
      JOIN bhp_stocks bs ON m.stock_id = bs.id
      JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
      WHERE m.maintenance_id IN (?) AND m.movement_type = 'maintenance_usage'
      ORDER BY m.id ASC
    `, [maintenanceIds]);

    return usageRows;
  },

  async findAssetForMaintenance(assetId, tx = null) {
    const conn = tx || db;
    const [assets] = await conn.query(`
      SELECT
        ia.id,
        ia.asset_condition,
        ia.status,
        ia.room_id,
        COALESCE(lproc.id, lroom.id) AS lab_id
      FROM inventory_assets ia
      LEFT JOIN rooms r ON ia.room_id = r.id
      LEFT JOIN laboratories lroom ON r.id = lroom.room_id
      LEFT JOIN procurement_items pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories lproc ON pd.lab_id = lproc.id
      WHERE ia.id = ?
      LIMIT 1
      FOR UPDATE
    `, [assetId]);

    return assets[0] || null;
  },

  async createLog({ inventoryAssetId, performedBy, maintenanceDate, issueDescription, actionTaken, conditionBefore, conditionAfter, status, cost, notes }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO maintenance_logs (
        inventory_asset_id, performed_by, maintenance_date,
        issue_description, action_taken, condition_before,
        condition_after, status, cost, notes
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [inventoryAssetId, performedBy, maintenanceDate, issueDescription, actionTaken, conditionBefore, conditionAfter, status, cost, notes]);
    return result;
  },

  async updateAssetStatus(assetId, condition, status, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE inventory_assets
      SET asset_condition = ?, status = ?, updated_at = NOW()
      WHERE id = ?
    `, [condition, status, assetId]);
    return result;
  },

  async findBhpStockForUpdate(stockId, tx = null) {
    const conn = tx || db;
    const [stocks] = await conn.query(`
      SELECT id, lab_id, current_stock
      FROM bhp_stocks
      WHERE id = ?
      LIMIT 1
      FOR UPDATE
    `, [stockId]);
    return stocks[0] || null;
  },

  async updateBhpStockQty(stockId, qtyToDeduct, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(
      "UPDATE bhp_stocks SET current_stock = current_stock - ? WHERE id = ?",
      [qtyToDeduct, stockId]
    );
    return result;
  },

  async createBhpStockMovement({ stockId, maintenanceId, performedBy, quantity, note }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO bhp_stock_movements (stock_id, maintenance_id, performed_by, movement_type, quantity, note)
      VALUES (?, ?, ?, 'maintenance_usage', ?, ?)
    `, [stockId, maintenanceId, performedBy, quantity, note || null]);
    return result;
  }
};

module.exports = MaintenanceModel;