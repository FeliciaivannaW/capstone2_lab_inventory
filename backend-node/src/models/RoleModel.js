const db = require("../config/database");

const RoleModel = {
  async findAll() {
    const [roles] = await db.query("SELECT * FROM roles ORDER BY id ASC");
    return roles;
  }
};

module.exports = RoleModel;
