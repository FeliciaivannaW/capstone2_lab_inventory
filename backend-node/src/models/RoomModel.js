const db = require("../config/database");

const RoomModel = {
  async findAll({ search, room_type_id, floor_id, building_id }) {
    const conditions = [];
    const params = [];

    if (search) {
      conditions.push("(rooms.code LIKE ? OR rooms.name LIKE ? OR buildings.name LIKE ? OR buildings.code LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`);
    }

    if (room_type_id) {
      conditions.push("rooms.room_type_id = ?");
      params.push(Number(room_type_id));
    }

    if (floor_id) {
      conditions.push("rooms.floor_id = ?");
      params.push(Number(floor_id));
    }

    if (building_id) {
      conditions.push("buildings.id = ?");
      params.push(Number(building_id));
    }

    const whereClause = conditions.length ? `WHERE ${conditions.join(" AND ")}` : "";

    const [rooms] = await db.query(`
      SELECT
        rooms.id,
        rooms.code,
        rooms.name,
        rooms.capacity,
        rooms.description,
        rooms.floor_id,
        floors.name AS floor_name,
        floors.floor_number,
        buildings.id AS building_id,
        buildings.code AS building_code,
        buildings.name AS building_name,
        rooms.room_type_id,
        room_types.name AS room_type,
        rooms.created_at,
        rooms.updated_at
      FROM rooms
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      JOIN room_types ON rooms.room_type_id = room_types.id
      ${whereClause}
      ORDER BY buildings.name ASC, floors.floor_number ASC, rooms.code ASC
    `, params);

    return rooms;
  },

  async findById(id) {
    const [rooms] = await db.query(`
      SELECT
        rooms.id,
        rooms.code,
        rooms.name,
        rooms.capacity,
        rooms.description,
        rooms.floor_id,
        floors.name AS floor_name,
        floors.floor_number,
        buildings.id AS building_id,
        buildings.code AS building_code,
        buildings.name AS building_name,
        rooms.room_type_id,
        room_types.name AS room_type
      FROM rooms
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      JOIN room_types ON rooms.room_type_id = room_types.id
      WHERE rooms.id = ?
      LIMIT 1
    `, [id]);

    return rooms[0] || null;
  },

  async findByCode(code, tx = null) {
    const conn = tx || db;
    const [rooms] = await conn.query("SELECT id FROM rooms WHERE code = ? LIMIT 1", [code]);
    return rooms[0] || null;
  },

  async findByCodeExcludeId(code, id, tx = null) {
    const conn = tx || db;
    const [rooms] = await conn.query("SELECT id FROM rooms WHERE code = ? AND id <> ? LIMIT 1", [code, id]);
    return rooms[0] || null;
  },

  async findBuildings() {
    const [buildings] = await db.query(`
      SELECT id, code, name, address, description
      FROM buildings
      ORDER BY name ASC
    `);
    return buildings;
  },

  async findFloors() {
    const [floors] = await db.query(`
      SELECT
        floors.id,
        floors.building_id,
        floors.name,
        floors.floor_number,
        buildings.code AS building_code,
        buildings.name AS building_name
      FROM floors
      JOIN buildings ON floors.building_id = buildings.id
      ORDER BY buildings.name ASC, floors.floor_number ASC
    `);
    return floors;
  },

  async findRoomTypes() {
    const [roomTypes] = await db.query("SELECT id, name, description FROM room_types ORDER BY name ASC");
    return roomTypes;
  },

  async createBuilding({ code, name, address, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO buildings (code, name, address, description)
      VALUES (?, ?, ?, ?)
    `, [code, name, address || null, description || null]);
    return result;
  },

  async createFloor({ building_id, floor_number, name, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO floors (building_id, floor_number, name, description)
      VALUES (?, ?, ?, ?)
    `, [building_id, floor_number, name, description || null]);
    return result;
  },

  async createRoomType({ name, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO room_types (name, description)
      VALUES (?, ?)
    `, [name, description || null]);
    return result;
  },

  async create({ floor_id, room_type_id, code, name, capacity, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO rooms (floor_id, room_type_id, code, name, capacity, description)
      VALUES (?, ?, ?, ?, ?, ?)
    `, [floor_id, room_type_id, code, name, capacity || null, description || null]);
    return result;
  },

  async createMany(rooms, tx = null) {
    const conn = tx || db;
    const values = rooms.map((room) => [
      room.floor_id,
      room.room_type_id,
      room.code,
      room.name,
      room.capacity || null,
      room.description || null
    ]);

    const [result] = await conn.query(`
      INSERT INTO rooms (floor_id, room_type_id, code, name, capacity, description)
      VALUES ?
    `, [values]);
    return result;
  },

  async update(id, { floor_id, room_type_id, code, name, capacity, description }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE rooms
      SET floor_id = ?, room_type_id = ?, code = ?, name = ?, capacity = ?, description = ?
      WHERE id = ?
    `, [floor_id, room_type_id, code, name, capacity || null, description || null, id]);
    return result;
  },

  async delete(id, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query("DELETE FROM rooms WHERE id = ?", [id]);
    return result;
  },

  async getOptions() {
    const [buildings] = await db.query(`
      SELECT id, code, name
      FROM buildings
      ORDER BY name ASC
    `);

    const [floors] = await db.query(`
      SELECT
        floors.id,
        floors.building_id,
        floors.floor_number,
        floors.name,
        buildings.code AS building_code,
        buildings.name AS building_name
      FROM floors
      JOIN buildings ON floors.building_id = buildings.id
      ORDER BY buildings.name ASC, floors.floor_number ASC
    `);

    const [roomTypes] = await db.query(`
      SELECT id, name, description
      FROM room_types
      ORDER BY name ASC
    `);

    return {
      buildings,
      floors,
      room_types: roomTypes
    };
  },

  async deleteBuilding(id) {
    const [result] = await db.query(
      "DELETE FROM buildings WHERE id = ?",
      [id]
    );

    return result;
  },

  async deleteFloor(id) {
    const [result] = await db.query(
      "DELETE FROM floors WHERE id = ?",
      [id]
    );

    return result;
  },

  async deleteRoomType(id) {
    const [result] = await db.query(
      "DELETE FROM room_types WHERE id = ?",
      [id]
    );

    return result;
  },
};

module.exports = RoomModel;