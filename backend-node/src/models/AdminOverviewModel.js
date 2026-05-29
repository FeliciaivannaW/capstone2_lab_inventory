const db = require("../config/database");

const AdminOverviewModel = {
  async getSummary() {
    const [[result]] = await db.query(`
      SELECT
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM rooms) AS total_rooms,
        (SELECT COUNT(*) FROM laboratories) AS total_laboratories,
        (SELECT COUNT(*) FROM lab_groups) AS total_lab_groups,
        (SELECT COUNT(*) FROM inventory_assets) AS total_inventory_assets,
        (SELECT COUNT(*) FROM bhp_stocks) AS total_bhp_items,
        (SELECT COUNT(*) FROM maintenance_logs) AS total_maintenance_logs,
        (SELECT COUNT(*) FROM procurement_drafts) AS total_procurement_drafts
    `);

    return result;
  },

  async getAssetsByCondition() {
    const [rows] = await db.query(`
      SELECT
        asset_condition,
        COUNT(*) AS total
      FROM inventory_assets
      GROUP BY asset_condition
      ORDER BY total DESC
    `);

    return rows;
  },

  async getLowBhpStocks() {
    const [rows] = await db.query(`
      SELECT
        bs.id,
        ic.name AS item_name,
        l.name AS laboratory_name,
        l.code AS laboratory_code,
        bs.current_stock,
        bs.minimum_stock,
        bs.unit
      FROM bhp_stocks bs
      JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
      JOIN laboratories l ON bs.lab_id = l.id
      WHERE bs.current_stock <= bs.minimum_stock
      ORDER BY bs.current_stock ASC, ic.name ASC
      LIMIT 10
    `);

    return rows;
  },

  async getMaintenanceSummary() {
    const [rows] = await db.query(`
      SELECT
        status,
        COUNT(*) AS total
      FROM maintenance_logs
      GROUP BY status
      ORDER BY total DESC
    `);

    return rows;
  },

  async getRoomSummary() {
    const [rows] = await db.query(`
      SELECT
        rt.name AS room_type,
        COUNT(r.id) AS total
      FROM rooms r
      JOIN room_types rt ON r.room_type_id = rt.id
      GROUP BY rt.name
      ORDER BY total DESC
    `);

    return rows;
  }
};

module.exports = AdminOverviewModel;