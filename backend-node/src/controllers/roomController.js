const db = require("../config/database");

const getRooms = async (req, res) => {
  try {
    const { search, room_type_id, floor_id } = req.query;
    const conditions = [];
    const params = [];

    if (search) {
      conditions.push("(rooms.code LIKE ? OR rooms.name LIKE ? OR buildings.name LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }

    if (room_type_id) {
      conditions.push("rooms.room_type_id = ?");
      params.push(Number(room_type_id));
    }

    if (floor_id) {
      conditions.push("rooms.floor_id = ?");
      params.push(Number(floor_id));
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

    res.json({ status: "success", data: rooms });
  } catch (error) {
    console.error("[GET ROOMS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data ruangan" });
  }
};

const getRoom = async (req, res) => {
  try {
    const [rooms] = await db.query(`
      SELECT
        rooms.id,
        rooms.code,
        rooms.name,
        rooms.capacity,
        rooms.description,
        rooms.floor_id,
        floors.name AS floor_name,
        buildings.name AS building_name,
        rooms.room_type_id,
        room_types.name AS room_type
      FROM rooms
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      JOIN room_types ON rooms.room_type_id = room_types.id
      WHERE rooms.id = ?
      LIMIT 1
    `, [req.params.id]);

    if (!rooms.length) {
      return res.status(404).json({ status: "error", message: "Ruangan tidak ditemukan" });
    }

    res.json({ status: "success", data: rooms[0] });
  } catch (error) {
    console.error("[GET ROOM ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil detail ruangan" });
  }
};

const getRoomOptions = async (req, res) => {
  try {
    const [floors] = await db.query(`
      SELECT floors.id, floors.name, floors.floor_number, buildings.name AS building_name
      FROM floors
      JOIN buildings ON floors.building_id = buildings.id
      ORDER BY buildings.name ASC, floors.floor_number ASC
    `);

    const [roomTypes] = await db.query("SELECT id, name, description FROM room_types ORDER BY name ASC");

    res.json({
      status: "success",
      data: {
        floors,
        room_types: roomTypes
      }
    });
  } catch (error) {
    console.error("[GET ROOM OPTIONS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil opsi ruangan" });
  }
};

const createRoom = async (req, res) => {
  try {
    const { floor_id, room_type_id, code, name, capacity, description } = req.body;

    if (!floor_id || !room_type_id || !code || !name) {
      return res.status(400).json({ status: "error", message: "Lantai, tipe ruangan, kode, dan nama ruangan wajib diisi" });
    }

    const [existing] = await db.query("SELECT id FROM rooms WHERE code = ? LIMIT 1", [code]);
    if (existing.length) {
      return res.status(409).json({ status: "error", message: "Kode ruangan sudah digunakan" });
    }

    const [result] = await db.query(`
      INSERT INTO rooms (floor_id, room_type_id, code, name, capacity, description)
      VALUES (?, ?, ?, ?, ?, ?)
    `, [Number(floor_id), Number(room_type_id), code, name, capacity || null, description || null]);

    res.status(201).json({ status: "success", message: "Ruangan berhasil ditambahkan", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE ROOM ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal menambahkan ruangan" });
  }
};

const updateRoom = async (req, res) => {
  try {
    const { floor_id, room_type_id, code, name, capacity, description } = req.body;
    const id = Number(req.params.id);

    if (!floor_id || !room_type_id || !code || !name) {
      return res.status(400).json({ status: "error", message: "Lantai, tipe ruangan, kode, dan nama ruangan wajib diisi" });
    }

    const [existing] = await db.query("SELECT id FROM rooms WHERE code = ? AND id <> ? LIMIT 1", [code, id]);
    if (existing.length) {
      return res.status(409).json({ status: "error", message: "Kode ruangan sudah digunakan" });
    }

    const [result] = await db.query(`
      UPDATE rooms
      SET floor_id = ?, room_type_id = ?, code = ?, name = ?, capacity = ?, description = ?
      WHERE id = ?
    `, [Number(floor_id), Number(room_type_id), code, name, capacity || null, description || null, id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({ status: "error", message: "Ruangan tidak ditemukan" });
    }

    res.json({ status: "success", message: "Ruangan berhasil diperbarui" });
  } catch (error) {
    console.error("[UPDATE ROOM ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal memperbarui ruangan" });
  }
};

const deleteRoom = async (req, res) => {
  try {
    const [result] = await db.query("DELETE FROM rooms WHERE id = ?", [req.params.id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({ status: "error", message: "Ruangan tidak ditemukan" });
    }

    res.json({ status: "success", message: "Ruangan berhasil dihapus" });
  } catch (error) {
    console.error("[DELETE ROOM ERROR]", error);
    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(409).json({ status: "error", message: "Ruangan tidak bisa dihapus karena masih dipakai oleh lab/inventaris" });
    }
    res.status(500).json({ status: "error", message: "Gagal menghapus ruangan" });
  }
};

module.exports = {
  getRooms,
  getRoom,
  getRoomOptions,
  createRoom,
  updateRoom,
  deleteRoom
};