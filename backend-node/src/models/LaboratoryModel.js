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
               OR lg.id IN (
                 SELECT lgl.group_id
                 FROM lab_group_laboratories lgl
                 WHERE lgl.laboratory_id = laboratories.id
               )
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
      SELECT *
      FROM laboratories
      WHERE id = ?
      LIMIT 1
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
      SELECT
        u.id,
        u.name,
        u.email
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE r.name = 'staf_laboratorium'
        AND u.status = 'active'
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
        (
          SELECT COUNT(DISTINCT lgu.user_id)
          FROM lab_group_users lgu
          WHERE lgu.group_id = lg.id
        ) AS total_users,
        (
          SELECT COUNT(DISTINCT access_rooms.room_id)
          FROM (
            SELECT lab.room_id AS room_id, lgl.group_id AS group_id
            FROM lab_group_laboratories lgl
            JOIN laboratories lab ON lab.id = lgl.laboratory_id
            WHERE lab.room_id IS NOT NULL

            UNION

            SELECT lgr.room_id AS room_id, lgr.group_id AS group_id
            FROM lab_group_rooms lgr
            WHERE lgr.room_id IS NOT NULL
          ) AS access_rooms
          WHERE access_rooms.group_id = lg.id
        ) AS total_rooms,
        COALESCE(
          (
            SELECT GROUP_CONCAT(DISTINCT CONCAT(gl.code, ' - ', gl.name) ORDER BY gl.name SEPARATOR ', ')
            FROM lab_group_laboratories lgl
            JOIN laboratories gl ON gl.id = lgl.laboratory_id
            WHERE lgl.group_id = lg.id
          ),
          CONCAT(l.code, ' - ', l.name)
        ) AS managed_lab_names,
        COALESCE(
          (
            SELECT GROUP_CONCAT(DISTINCT gl.id ORDER BY gl.id SEPARATOR ',')
            FROM lab_group_laboratories lgl
            JOIN laboratories gl ON gl.id = lgl.laboratory_id
            WHERE lgl.group_id = lg.id
          ),
          CAST(lg.laboratory_id AS CHAR)
        ) AS managed_lab_ids,
        (
          SELECT GROUP_CONCAT(DISTINCT CONCAT(rm.code, ' - ', rm.name) ORDER BY rm.code SEPARATOR ', ')
          FROM rooms rm
          WHERE rm.id IN (
            SELECT lab.room_id
            FROM lab_group_laboratories lgl
            JOIN laboratories lab ON lab.id = lgl.laboratory_id
            WHERE lgl.group_id = lg.id
              AND lab.room_id IS NOT NULL

            UNION

            SELECT lgr.room_id
            FROM lab_group_rooms lgr
            WHERE lgr.group_id = lg.id
              AND lgr.room_id IS NOT NULL
          )
        ) AS managed_room_names
      FROM lab_groups lg
      JOIN laboratories l ON lg.laboratory_id = l.id
      ORDER BY l.name ASC, lg.name ASC
    `);

    return rows.map((row) => ({
      ...row,
      managed_lab_names: row.managed_lab_names || `${row.laboratory_code} - ${row.laboratory_name}`,
      managed_lab_ids: row.managed_lab_ids || String(row.laboratory_id),
      managed_room_names: row.managed_room_names || null
    }));
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

    const [result] = await conn.query(
      "DELETE FROM laboratories WHERE id = ?",
      [id]
    );

    return result;
  },

  async syncGroupLaboratories(groupId, labIds, tx = null) {
    const conn = tx || db;

    const uniqueLabIds = [...new Set((labIds || [])
      .map((id) => Number(id))
      .filter((id) => Number.isInteger(id) && id > 0)
    )];

    await conn.query(
      "DELETE FROM lab_group_laboratories WHERE group_id = ?",
      [groupId]
    );

    for (const labId of uniqueLabIds) {
      await conn.query(`
        INSERT INTO lab_group_laboratories (group_id, laboratory_id)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE laboratory_id = VALUES(laboratory_id)
      `, [groupId, labId]);
    }
  },

  async createGroup({ laboratory_id, lab_ids, name, description }, tx = null) {
    const conn = tx || db;

    const selectedLabIds = [...new Set((lab_ids || [])
      .map((id) => Number(id))
      .filter((id) => Number.isInteger(id) && id > 0)
    )];

    const primaryLabId = selectedLabIds[0] || Number(laboratory_id);

    const [result] = await conn.query(`
      INSERT INTO lab_groups (laboratory_id, name, description)
      VALUES (?, ?, ?)
    `, [primaryLabId, name, description || null]);

    await this.syncGroupLaboratories(
      result.insertId,
      selectedLabIds.length ? selectedLabIds : [primaryLabId],
      conn
    );

    return result;
  },

  async updateGroup(id, { laboratory_id, lab_ids, name, description }) {
    const selectedLabIds = [...new Set((lab_ids || [])
      .map((labId) => Number(labId))
      .filter((labId) => Number.isInteger(labId) && labId > 0)
    )];

    const primaryLabId = selectedLabIds[0] || Number(laboratory_id);

    const [result] = await db.query(`
      UPDATE lab_groups
      SET laboratory_id = ?, name = ?, description = ?
      WHERE id = ?
    `, [primaryLabId, name, description || null, id]);

    await this.syncGroupLaboratories(
      id,
      selectedLabIds.length ? selectedLabIds : [primaryLabId]
    );

    return result;
  },

  async deleteGroup(id, tx = null) {
    const conn = tx || db;

    const [result] = await conn.query(
      "DELETE FROM lab_groups WHERE id = ?",
      [id]
    );

    return result;
  },

  async addUserToGroup({ group_id, user_id, role_in_group = "staf_lab" }, tx = null) {
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
        l.name AS laboratory_name,
        COALESCE(
          (
            SELECT GROUP_CONCAT(DISTINCT CONCAT(gl.code, ' - ', gl.name) ORDER BY gl.name SEPARATOR ', ')
            FROM lab_group_laboratories lgl
            JOIN laboratories gl ON gl.id = lgl.laboratory_id
            WHERE lgl.group_id = lg.id
          ),
          CONCAT(l.code, ' - ', l.name)
        ) AS managed_lab_names,
        COALESCE(
          (
            SELECT GROUP_CONCAT(DISTINCT gl.id ORDER BY gl.id SEPARATOR ',')
            FROM lab_group_laboratories lgl
            JOIN laboratories gl ON gl.id = lgl.laboratory_id
            WHERE lgl.group_id = lg.id
          ),
          CAST(lg.laboratory_id AS CHAR)
        ) AS managed_lab_ids
      FROM lab_groups lg
      JOIN laboratories l ON lg.laboratory_id = l.id
      WHERE lg.id = ?
      LIMIT 1
    `, [id]);

    return rows[0] || null;
  },

  async findGroupLabs(groupId) {
    const [rows] = await db.query(`
      SELECT
        l.id,
        l.code,
        l.name
      FROM laboratories l
      WHERE l.id IN (
        SELECT lgl.laboratory_id
        FROM lab_group_laboratories lgl
        WHERE lgl.group_id = ?

        UNION

        SELECT lg.laboratory_id
        FROM lab_groups lg
        WHERE lg.id = ?
          AND NOT EXISTS (
            SELECT 1
            FROM lab_group_laboratories lgl2
            WHERE lgl2.group_id = lg.id
          )
      )
      ORDER BY l.name ASC
    `, [groupId, groupId]);

    return rows;
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
      SELECT DISTINCT
        rm.id AS room_id,
        rm.code AS room_code,
        rm.name AS room_name,
        f.name AS floor_name,
        b.name AS building_name,
        NULL AS assignment_id,
        NULL AS created_at
      FROM rooms rm
      JOIN floors f ON rm.floor_id = f.id
      JOIN buildings b ON f.building_id = b.id
      WHERE rm.id IN (
        SELECT lab.room_id
        FROM lab_group_laboratories lgl
        JOIN laboratories lab ON lab.id = lgl.laboratory_id
        WHERE lgl.group_id = ?
          AND lab.room_id IS NOT NULL

        UNION

        SELECT lgr.room_id
        FROM lab_group_rooms lgr
        WHERE lgr.group_id = ?
          AND lgr.room_id IS NOT NULL
      )
      ORDER BY b.name ASC, f.floor_number ASC, rm.code ASC
    `, [groupId, groupId]);

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