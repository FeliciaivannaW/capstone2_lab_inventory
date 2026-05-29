const db = require("../config/database");

const UserModel = {
  async findAll({ role, status, search }) {
    const conditions = [];
    const params = [];

    if (role) {
      conditions.push("r.name = ?");
      params.push(role);
    }

    if (status) {
      conditions.push("u.status = ?");
      params.push(status);
    }

    if (search) {
      conditions.push("(u.name LIKE ? OR u.email LIKE ? OR u.nrp_nip LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }

    const whereClause = conditions.length ? `WHERE ${conditions.join(" AND ")}` : "";

    const [users] = await db.query(`
      SELECT
        u.id,
        u.name,
        u.nrp_nip,
        u.email,
        u.status,
        u.role_id,
        r.name AS role,
        u.lab_id,
        l.name AS laboratory_name,
        u.created_at,
        u.updated_at,
        (
          SELECT GROUP_CONCAT(lg.id ORDER BY lg.name SEPARATOR ',')
          FROM lab_group_users lgu
          JOIN lab_groups lg ON lg.id = lgu.group_id
          WHERE lgu.user_id = u.id
        ) AS lab_group_ids,
        (
          SELECT GROUP_CONCAT(CONCAT(l.name, ' - ', lg.name) ORDER BY l.name, lg.name SEPARATOR ', ')
          FROM lab_group_users lgu
          JOIN lab_groups lg ON lg.id = lgu.group_id
          JOIN laboratories l ON l.id = lg.laboratory_id
          WHERE lgu.user_id = u.id
        ) AS lab_group_names
      FROM users u
      JOIN roles r ON u.role_id = r.id
      LEFT JOIN laboratories l ON u.lab_id = l.id
      ${whereClause}
      ORDER BY u.created_at DESC, u.id DESC
    `, params);

    return users;
  },

  async findById(id) {
    const [users] = await db.query(`
      SELECT
        u.id,
        u.name,
        u.nrp_nip,
        u.email,
        u.status,
        u.role_id,
        r.name AS role,
        u.lab_id,
        l.name AS laboratory_name,
        u.created_at,
        u.updated_at,
        (
          SELECT GROUP_CONCAT(lg.id ORDER BY lg.name SEPARATOR ',')
          FROM lab_group_users lgu
          JOIN lab_groups lg ON lg.id = lgu.group_id
          WHERE lgu.user_id = u.id
        ) AS lab_group_ids,
        (
          SELECT GROUP_CONCAT(CONCAT(l.name, ' - ', lg.name) ORDER BY l.name, lg.name SEPARATOR ', ')
          FROM lab_group_users lgu
          JOIN lab_groups lg ON lg.id = lgu.group_id
          JOIN laboratories l ON l.id = lg.laboratory_id
          WHERE lgu.user_id = u.id
        ) AS lab_group_names
      FROM users u
      JOIN roles r ON u.role_id = r.id
      LEFT JOIN laboratories l ON u.lab_id = l.id
      WHERE u.id = ?
      LIMIT 1
    `, [id]);

    return users[0] || null;
  },

  async findByEmail(email) {
    const [users] = await db.query(
      "SELECT id FROM users WHERE email = ? LIMIT 1",
      [email]
    );
    return users[0] || null;
  },

  async findByEmailExcludeId(email, id) {
    const [users] = await db.query(
      "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1",
      [email, id]
    );
    return users[0] || null;
  },

  async findByEmailForAuth(email) {
    const [users] = await db.query(`
      SELECT
        users.id,
        users.name,
        users.email,
        users.password,
        users.status,
        users.lab_id,
        roles.name AS role,
        laboratories.name AS laboratory_name
      FROM users
      JOIN roles ON users.role_id = roles.id
      LEFT JOIN laboratories ON users.lab_id = laboratories.id
      WHERE users.email = ?
      LIMIT 1
    `, [email]);

    return users[0] || null;
  },

  async findByIdForAuth(id) {
    const [users] = await db.query(`
      SELECT
        users.id,
        users.name,
        users.email,
        users.status,
        roles.name AS role,
        laboratories.name AS laboratory_name
      FROM users
      JOIN roles ON users.role_id = roles.id
      LEFT JOIN laboratories ON users.lab_id = laboratories.id
      WHERE users.id = ?
      LIMIT 1
    `, [id]);

    return users[0] || null;
  },

  async findRoleAndLabByUserId(id) {
    const [rows] = await db.query(`
      SELECT u.id, u.lab_id, r.name AS role
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.id = ?
      LIMIT 1
    `, [id]);
    return rows[0] || null;
  },

  async create({ role_id, lab_id, name, nrp_nip, email, password, status }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password, status)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `, [role_id, lab_id, name, nrp_nip || null, email, password, status]);

    return result;
  },

  async update(id, { role_id, lab_id, name, nrp_nip, email, password, status }, tx = null) {
    const conn = tx || db;

    if (password) {
      const [result] = await conn.query(`
        UPDATE users
        SET role_id = ?, lab_id = ?, name = ?, nrp_nip = ?, email = ?, password = ?, status = ?
        WHERE id = ?
      `, [role_id, lab_id, name, nrp_nip || null, email, password, status, id]);

      return result;
    }

    const [result] = await conn.query(`
      UPDATE users
      SET role_id = ?, lab_id = ?, name = ?, nrp_nip = ?, email = ?, status = ?
      WHERE id = ?
    `, [role_id, lab_id, name, nrp_nip || null, email, status, id]);

    return result;
  },

  async syncLabGroups(userId, groupIds = [], tx = null) {
    const conn = tx || db;
    const uniqueGroupIds = [...new Set(
      (Array.isArray(groupIds) ? groupIds : [])
        .map(Number)
        .filter((id) => Number.isInteger(id) && id > 0)
    )];

    await conn.query("DELETE FROM lab_group_users WHERE user_id = ?", [userId]);

    for (const groupId of uniqueGroupIds) {
      await conn.query(`
        INSERT INTO lab_group_users (group_id, user_id, role_in_group)
        VALUES (?, ?, 'staf_lab')
        ON DUPLICATE KEY UPDATE role_in_group = VALUES(role_in_group)
      `, [groupId, userId]);
    }
  },

  async deleteLabGroupAccess(userId, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(
      "DELETE FROM lab_group_users WHERE user_id = ?",
      [userId]
    );

    return result;
  },

  async delete(id, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query("DELETE FROM users WHERE id = ?", [id]);
    return result;
  }
};

module.exports = UserModel;