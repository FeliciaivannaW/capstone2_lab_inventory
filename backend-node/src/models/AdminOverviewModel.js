const db = require("../config/database");

const AdminOverviewModel = {
  async getOverview() {
    const [[rooms]] = await db.query("SELECT COUNT(*) AS total_rooms FROM rooms");
    const [[laboratories]] = await db.query("SELECT COUNT(*) AS total_laboratories FROM laboratories");
    const [[users]] = await db.query("SELECT COUNT(*) AS total_users FROM users");
    const [[activeUsers]] = await db.query("SELECT COUNT(*) AS total_active_users FROM users WHERE status = 'active'");
    const [[bhpItems]] = await db.query("SELECT COUNT(*) AS total_bhp_stocks FROM bhp_stocks");
    const [[assets]] = await db.query("SELECT COUNT(*) AS total_assets FROM inventory_assets");

    const [roleSummary] = await db.query(`
      SELECT r.name AS role, COUNT(u.id) AS total
      FROM roles r
      LEFT JOIN users u ON u.role_id = r.id
      GROUP BY r.id, r.name
      ORDER BY r.name ASC
    `);

    return {
      total_rooms: rooms.total_rooms,
      total_laboratories: laboratories.total_laboratories,
      total_users: users.total_users,
      total_active_users: activeUsers.total_active_users,
      total_bhp_stocks: bhpItems.total_bhp_stocks,
      total_assets: assets.total_assets,
      role_summary: roleSummary
    };
  }
};

module.exports = AdminOverviewModel;