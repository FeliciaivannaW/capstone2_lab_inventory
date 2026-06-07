const db = require("../config/database");

const uniqueNumbers = (values) => [...new Set(values.map(Number).filter((value) => Number.isInteger(value) && value > 0))];

const splitNames = (value) => (value ? String(value).split(",").map((item) => item.trim()).filter(Boolean) : []);

const LabAccessModel = {
  async findCurrentUser(userId) {
    const [rows] = await db.query(`
      SELECT
        u.id,
        u.lab_id,
        r.name AS role,
        l.code AS laboratory_code,
        l.name AS laboratory_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      LEFT JOIN laboratories l ON u.lab_id = l.id
      WHERE u.id = ?
      LIMIT 1
    `, [userId]);
    return rows[0] || null;
  },

  async findGroupLabIds(userId) {
    const [rows] = await db.query(`
      SELECT lab_id FROM (
        SELECT lg.laboratory_id AS lab_id
        FROM lab_group_users lgu
        JOIN lab_groups lg ON lgu.group_id = lg.id
        WHERE lgu.user_id = ?

        UNION

        SELECT l_room.id AS lab_id
        FROM lab_group_users lgu
        JOIN lab_group_rooms lgr ON lgu.group_id = lgr.group_id
        JOIN rooms rm ON rm.id = lgr.room_id
        JOIN laboratories l_room ON l_room.room_id = rm.id
        WHERE lgu.user_id = ?
      ) AS access_labs
      WHERE lab_id IS NOT NULL
    `, [userId, userId]);

    return uniqueNumbers(rows.map((row) => row.lab_id));
  },

  async findGroupRoomIds(userId) {
    const [rows] = await db.query(`
      SELECT room_id FROM (
        SELECT l.room_id AS room_id
        FROM lab_group_users lgu
        JOIN lab_groups lg ON lgu.group_id = lg.id
        JOIN laboratories l ON l.id = lg.laboratory_id
        WHERE lgu.user_id = ? AND l.room_id IS NOT NULL

        UNION

        SELECT lgr.room_id AS room_id
        FROM lab_group_users lgu
        JOIN lab_group_rooms lgr ON lgu.group_id = lgr.group_id
        WHERE lgu.user_id = ?
      ) AS access_rooms
      WHERE room_id IS NOT NULL
    `, [userId, userId]);

    return uniqueNumbers(rows.map((row) => row.room_id));
  },

  async findAccessibleLabIds(userId) {
    const currentUser = await this.findCurrentUser(userId);
    if (!currentUser) return [];

    if (currentUser.role === "staf_laboratorium") {
      const groupLabIds = await this.findGroupLabIds(userId);

      if (groupLabIds.length) {
        return groupLabIds;
      }

      return currentUser.lab_id ? [Number(currentUser.lab_id)] : [];
    }

    return currentUser.lab_id ? [Number(currentUser.lab_id)] : [];
  },

  async findAccessibleRoomIds(userId) {
    const currentUser = await this.findCurrentUser(userId);
    if (!currentUser) return [];

    if (currentUser.role === "staf_laboratorium") {
      const groupRoomIds = await this.findGroupRoomIds(userId);

      if (groupRoomIds.length) {
        return groupRoomIds;
      }
    }

    if (!currentUser.lab_id) return [];

    const [rows] = await db.query(`
      SELECT room_id
      FROM laboratories
      WHERE id = ? AND room_id IS NOT NULL
      LIMIT 1
    `, [currentUser.lab_id]);

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
        lg.description AS group_description,
        lg.laboratory_id,
        l.code AS laboratory_code,
        l.name AS laboratory_name,
        lgu.role_in_group,
        GROUP_CONCAT(
          DISTINCT COALESCE(l_room.name, rm.name)
          ORDER BY COALESCE(l_room.name, rm.name)
          SEPARATOR ', '
        ) AS managed_lab_names,
        GROUP_CONCAT(
          DISTINCT CONCAT(rm.code, ' - ', rm.name)
          ORDER BY rm.code
          SEPARATOR ', '
        ) AS managed_room_names
      FROM lab_group_users lgu
      JOIN lab_groups lg ON lgu.group_id = lg.id
      JOIN laboratories l ON lg.laboratory_id = l.id
      LEFT JOIN lab_group_rooms lgr ON lgr.group_id = lg.id
      LEFT JOIN rooms rm ON rm.id = lgr.room_id
      LEFT JOIN laboratories l_room ON l_room.room_id = rm.id
      WHERE lgu.user_id = ?
      GROUP BY lg.id, lg.name, lg.description, lg.laboratory_id, l.code, l.name, lgu.role_in_group
      ORDER BY l.name ASC, lg.name ASC
    `, [userId]);

    return rows.map((row) => ({
      ...row,
      managed_lab_names: row.managed_lab_names || row.laboratory_name,
      managed_room_names: row.managed_room_names || null
    }));
  },

  async findAccessSummary(userId) {
    const currentUser = await this.findCurrentUser(userId);
    if (!currentUser) return null;

    const [groups, accessibleLabIds, accessibleRoomIds] = await Promise.all([
      this.findGroupsByUserId(userId),
      this.findAccessibleLabIds(userId),
      this.findAccessibleRoomIds(userId)
    ]);

    const [accessibleLabs] = accessibleLabIds.length
      ? await db.query(`
          SELECT id, code, name
          FROM laboratories
          WHERE id IN (?)
          ORDER BY name ASC
        `, [accessibleLabIds])
      : [[]];

    const [accessibleRooms] = accessibleRoomIds.length
      ? await db.query(`
          SELECT
            rm.id,
            rm.code,
            rm.name,
            COALESCE(l.name, rm.name) AS laboratory_name,
            COALESCE(l.code, rm.code) AS laboratory_code,
            b.name AS building_name,
            f.name AS floor_name
          FROM rooms rm
          JOIN floors f ON rm.floor_id = f.id
          JOIN buildings b ON f.building_id = b.id
          LEFT JOIN laboratories l ON l.room_id = rm.id
          WHERE rm.id IN (?)
          ORDER BY b.name ASC, f.floor_number ASC, rm.code ASC
        `, [accessibleRoomIds])
      : [[]];

    return {
      user: {
        id: currentUser.id,
        role: currentUser.role,
        lab_id: currentUser.lab_id,
        laboratory_code: currentUser.laboratory_code,
        laboratory_name: currentUser.laboratory_name
      },
      lab_utama: currentUser.lab_id ? {
        id: currentUser.lab_id,
        code: currentUser.laboratory_code,
        name: currentUser.laboratory_name
      } : null,
      groups: groups.map((group) => ({
        ...group,
        managed_labs: splitNames(group.managed_lab_names),
        managed_rooms: splitNames(group.managed_room_names)
      })),
      accessible_labs: accessibleLabs,
      accessible_rooms: accessibleRooms
    };
  }
};

module.exports = LabAccessModel;
