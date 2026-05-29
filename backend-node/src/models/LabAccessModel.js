const db = require("../config/database");

const uniqueNumbers = (values) => [...new Set(values.map(Number).filter((value) => Number.isInteger(value) && value > 0))];

const LabAccessModel = {
  async findCurrentUser(userId) {
    const [rows] = await db.query(`
      SELECT u.id, u.lab_id, r.name AS role
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.id = ?
      LIMIT 1
    `, [userId]);
    return rows[0] || null;
  },

  async findAccessibleLabIds(userId) {
    const [rows] = await db.query(`
      SELECT lab_id FROM (
        SELECT u.lab_id AS lab_id
        FROM users u
        WHERE u.id = ? AND u.lab_id IS NOT NULL
        UNION
        SELECT lg.laboratory_id AS lab_id
        FROM lab_group_users lgu
        JOIN lab_groups lg ON lgu.group_id = lg.id
        WHERE lgu.user_id = ?
      ) AS access_labs
    `, [userId, userId]);

    return uniqueNumbers(rows.map((row) => row.lab_id));
  },

  async findAccessibleRoomIds(userId) {
    const [rows] = await db.query(`
      SELECT room_id FROM (
        SELECT l.room_id AS room_id
        FROM users u
        JOIN laboratories l ON u.lab_id = l.id
        WHERE u.id = ? AND u.lab_id IS NOT NULL
        UNION
        SELECT lgr.room_id AS room_id
        FROM lab_group_users lgu
        JOIN lab_group_rooms lgr ON lgu.group_id = lgr.group_id
        WHERE lgu.user_id = ?
      ) AS access_rooms
    `, [userId, userId]);

    return uniqueNumbers(rows.map((row) => row.room_id));
  },

  async hasLabAccess(userId, labId) {
    const labIds = await this.findAccessibleLabIds(userId);
    return labIds.includes(Number(labId));
  },

  async hasRoomAccess(userId, roomId) {
    const roomIds = await this.findAccessibleRoomIds(userId);
    return roomIds.includes(Number(roomId));
  },

  async findGroupsByUserId(userId) {
    const [rows] = await db.query(`
      SELECT
        lg.id AS group_id,
        lg.name AS group_name,
        lg.laboratory_id,
        l.code AS laboratory_code,
        l.name AS laboratory_name,
        lgu.role_in_group
      FROM lab_group_users lgu
      JOIN lab_groups lg ON lgu.group_id = lg.id
      JOIN laboratories l ON lg.laboratory_id = l.id
      WHERE lgu.user_id = ?
      ORDER BY l.name ASC, lg.name ASC
    `, [userId]);
    return rows;
  }
};

module.exports = LabAccessModel;