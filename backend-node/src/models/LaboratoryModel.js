const db = require("../config/database");

const LaboratoryModel = {
  async findAll() {
    const [laboratories] = await db.query(`
      SELECT
        laboratories.id,
        laboratories.code,
        laboratories.name,
        laboratories.description,
        laboratories.room_id,
        rooms.code AS room_code,
        rooms.name AS room_name,
        floors.name AS floor_name,
        floors.floor_number,
        buildings.name AS building_name,
        users.name AS head_name,
        laboratories.head_user_id,
        COALESCE(
          users.name,
          (
            SELECT GROUP_CONCAT(DISTINCT u2.name ORDER BY u2.name SEPARATOR ', ')
            FROM lab_groups lg
            JOIN lab_group_users lgu ON lgu.group_id = lg.id
            JOIN users u2 ON u2.id = lgu.user_id
            WHERE lg.laboratory_id = laboratories.id
          ),
          'Belum ditentukan'
        ) AS responsible_name
      FROM laboratories
      JOIN rooms ON laboratories.room_id = rooms.id
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      LEFT JOIN users ON laboratories.head_user_id = users.id
      ORDER BY buildings.name ASC, floors.floor_number ASC, laboratories.name ASC
    `);
    return laboratories;
  },

  async findById(id) {
    const [rows] = await db.query(`
      SELECT * FROM laboratories WHERE id = ? LIMIT 1
    `, [id]);
    return rows[0] || null;
  },

  async findAvailableLabRooms() {
    const [rows] = await db.query(`
      SELECT
        r.id,
        r.code,
        r.name,
        f.name AS floor_name,
        f.floor_number,
        b.name AS building_name,
        rt.name AS room_type
      FROM rooms r
      JOIN room_types rt ON r.room_type_id = rt.id
      JOIN floors f ON r.floor_id = f.id
      JOIN buildings b ON f.building_id = b.id
      WHERE rt.name = 'laboratory'
      ORDER BY b.name ASC, f.floor_number ASC, r.code ASC
    `);
    return rows;
  },

  async findHeads() {
    const [rows] = await db.query(`
      SELECT
        u.id,
        u.name,
        u.email,
        r.name AS role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE r.name IN ('kepala_laboratorium', 'staf_laboratorium')
        AND u.status = 'active'
      ORDER BY
        FIELD(r.name, 'kepala_laboratorium', 'staf_laboratorium'),
        u.name ASC
    `);
    return rows;
  },

  async findStaffLabUsers() {
    const [rows] = await db.query(`
      SELECT u.id, u.name, u.email
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE r.name = 'staf_laboratorium' AND u.status = 'active'
      ORDER BY u.name ASC
    `);
    return rows;
  },

  async findLabGroups() {
    const [rows] = await db.query(`
      SELECT
        lg.id,
        lg.name,
        lg.description,
        lg.laboratory_id,
        l.code AS laboratory_code,
        l.name AS laboratory_name,
        COUNT(DISTINCT lgu.user_id) AS total_users,
        COUNT(DISTINCT lgr.room_id) AS total_rooms
      FROM lab_groups lg
      JOIN laboratories l ON lg.laboratory_id = l.id
      LEFT JOIN lab_group_users lgu ON lgu.group_id = lg.id
      LEFT JOIN lab_group_rooms lgr ON lgr.group_id = lg.id
      GROUP BY lg.id, lg.name, lg.description, lg.laboratory_id, l.code, l.name
      ORDER BY l.name ASC, lg.name ASC
    `);
    return rows;
  },

  async create({ room_id, head_user_id, name, code, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO laboratories (room_id, head_user_id, name, code, description)
      VALUES (?, ?, ?, ?, ?)
    `, [room_id, head_user_id || null, name, code, description || null]);
    return result;
  },

  async update(id, { room_id, head_user_id, name, code, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE laboratories
      SET room_id = ?, head_user_id = ?, name = ?, code = ?, description = ?
      WHERE id = ?
    `, [room_id, head_user_id || null, name, code, description || null, id]);
    return result;
  },

  async delete(id, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query("DELETE FROM laboratories WHERE id = ?", [id]);
    return result;
  },

  async createGroup({ laboratory_id, name, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO lab_groups (laboratory_id, name, description)
      VALUES (?, ?, ?)
    `, [laboratory_id, name, description || null]);
    return result;
  },

  async updateGroup(id, { laboratory_id, name, description }) {
    const [result] = await db.query(`
      UPDATE lab_groups
      SET laboratory_id = ?, name = ?, description = ?
      WHERE id = ?
    `, [laboratory_id, name, description || null, id]);
    return result;
  },

  async deleteGroup(id, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query("DELETE FROM lab_groups WHERE id = ?", [id]);
    return result;
  },

  async addUserToGroup({ group_id, user_id, role_in_group = 'staf_lab' }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO lab_group_users (group_id, user_id, role_in_group)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE role_in_group = VALUES(role_in_group)
    `, [group_id, user_id, role_in_group]);
    return result;
  },

  async addRoomToGroup({ group_id, room_id }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO lab_group_rooms (group_id, room_id)
      VALUES (?, ?)
      ON DUPLICATE KEY UPDATE room_id = VALUES(room_id)
    `, [group_id, room_id]);
    return result;
  },

  async findGroupById(id) {
    const [rows] = await db.query(`
      SELECT
        lg.id,
        lg.name,
        lg.description,
        lg.laboratory_id,
        l.code AS laboratory_code,
        l.name AS laboratory_name
      FROM lab_groups lg
      JOIN laboratories l ON lg.laboratory_id = l.id
      WHERE lg.id = ? LIMIT 1
    `, [id]);
    return rows[0] || null;
  },

  async findGroupUsers(groupId) {
    const [rows] = await db.query(`
      SELECT
        lgu.id AS assignment_id,
        lgu.user_id,
        u.name,
        u.email,
        r.name AS role_name,
        lgu.role_in_group,
        lgu.created_at
      FROM lab_group_users lgu
      JOIN users u ON lgu.user_id = u.id
      JOIN roles r ON u.role_id = r.id
      WHERE lgu.group_id = ?
      ORDER BY lgu.role_in_group ASC, u.name ASC
    `, [groupId]);
    return rows;
  },

  async findGroupRooms(groupId) {
    const [rows] = await db.query(`
      SELECT
        lgr.id AS assignment_id,
        lgr.room_id,
        rm.code AS room_code,
        rm.name AS room_name,
        f.name AS floor_name,
        b.name AS building_name,
        lgr.created_at
      FROM lab_group_rooms lgr
      JOIN rooms rm ON lgr.room_id = rm.id
      JOIN floors f ON rm.floor_id = f.id
      JOIN buildings b ON f.building_id = b.id
      WHERE lgr.group_id = ?
      ORDER BY b.name ASC, f.floor_number ASC, rm.code ASC
    `, [groupId]);
    return rows;
  },

  async removeUserFromGroup(groupId, userId) {
    const [result] = await db.query(
      "DELETE FROM lab_group_users WHERE group_id = ? AND user_id = ?",
      [groupId, userId]
    );
    return result;
  },

  async removeRoomFromGroup(groupId, roomId) {
    const [result] = await db.query(
      "DELETE FROM lab_group_rooms WHERE group_id = ? AND room_id = ?",
      [groupId, roomId]
    );
    return result;
  },

  async getOptions() {
    const [availableRooms] = await db.query(`
      SELECT
        rooms.id,
        rooms.code,
        rooms.name,
        room_types.name AS room_type,
        floors.name AS floor_name,
        floors.floor_number,
        buildings.name AS building_name,
        buildings.code AS building_code
      FROM rooms
      JOIN room_types ON rooms.room_type_id = room_types.id
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      WHERE room_types.name = 'laboratory'
      ORDER BY buildings.name ASC, floors.floor_number ASC, rooms.code ASC
    `);

    const [heads] = await db.query(`
      SELECT
        users.id,
        users.name,
        users.email,
        roles.name AS role_name
      FROM users
      JOIN roles ON users.role_id = roles.id
      WHERE users.status = 'active'
        AND roles.name IN ('kepala_laboratorium', 'staf_laboratorium')
      ORDER BY
        FIELD(roles.name, 'kepala_laboratorium', 'staf_laboratorium'),
        users.name ASC
    `);

    return {
      available_rooms: availableRooms,
      heads
    };
  },
};

module.exports = LaboratoryModel;