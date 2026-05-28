const db = require("../config/database");
const RoomModel = require("../models/RoomModel");

const toPositiveInt = (value, fieldName, required = true) => {
  if ((value === undefined || value === null || value === "") && !required) return null;
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) {
    const error = new Error(`${fieldName} harus berupa angka lebih dari 0`);
    error.statusCode = 400;
    throw error;
  }
  return parsed;
};

const normalizeRoomPayload = (body) => ({
  floor_id: toPositiveInt(body.floor_id, "Lantai"),
  room_type_id: toPositiveInt(body.room_type_id, "Tipe ruangan"),
  code: String(body.code || "").trim().toUpperCase(),
  name: String(body.name || "").trim(),
  capacity: body.capacity === "" || body.capacity === undefined || body.capacity === null ? null : Number(body.capacity),
  description: body.description ? String(body.description).trim() : null
});

const validateRoomPayload = (room) => {
  if (!room.code) {
    const error = new Error("Kode ruangan wajib diisi");
    error.statusCode = 400;
    throw error;
  }

  if (!room.name) {
    const error = new Error("Nama ruangan wajib diisi");
    error.statusCode = 400;
    throw error;
  }

  if (room.capacity !== null && (!Number.isInteger(room.capacity) || room.capacity < 0)) {
    const error = new Error("Kapasitas harus berupa angka minimal 0");
    error.statusCode = 400;
    throw error;
  }

  return room;
};

const getRooms = async (req, res) => {
  try {
    const rooms = await RoomModel.findAll(req.query);
    res.json({ status: "success", data: rooms });
  } catch (error) {
    console.error("[GET ROOMS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data ruangan" });
  }
};

const getRoom = async (req, res) => {
  try {
    const room = await RoomModel.findById(req.params.id);
    if (!room) {
      return res.status(404).json({ status: "error", message: "Ruangan tidak ditemukan" });
    }

    res.json({ status: "success", data: room });
  } catch (error) {
    console.error("[GET ROOM ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil detail ruangan" });
  }
};

const getRoomOptions = async (req, res) => {
  try {
    const options = await RoomModel.getOptions();

    res.json({
      status: "success",
      data: options
    });
  } catch (error) {
    console.error("[GET ROOM OPTIONS ERROR]", error);

    res.status(500).json({
      status: "error",
      message: "Gagal mengambil opsi ruangan"
    });
  }
};

const createBuilding = async (req, res) => {
  try {
    const payload = {
      code: String(req.body.code || "").trim().toUpperCase(),
      name: String(req.body.name || "").trim(),
      address: req.body.address ? String(req.body.address).trim() : null,
      description: req.body.description ? String(req.body.description).trim() : null
    };

    if (!payload.code || !payload.name) {
      return res.status(400).json({ status: "error", message: "Kode dan nama gedung wajib diisi" });
    }

    const result = await RoomModel.createBuilding(payload);
    res.status(201).json({ status: "success", message: "Gedung berhasil ditambahkan", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE BUILDING ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Kode gedung sudah digunakan" });
    }
    res.status(500).json({ status: "error", message: "Gagal menambahkan gedung" });
  }
};

const createFloor = async (req, res) => {
  try {
    const buildingId = toPositiveInt(req.body.building_id, "Gedung");
    const floorNumber = Number(req.body.floor_number);

    if (!Number.isInteger(floorNumber)) {
      return res.status(400).json({ status: "error", message: "Nomor lantai harus berupa angka" });
    }

   const payload = {
      building_id: buildingId,
      floor_number: floorNumber,
      name: String(req.body.name || `Lantai ${floorNumber}`).trim(),
      description: req.body.description ? String(req.body.description).trim() : null
    };

    const result = await RoomModel.createFloor(payload);
    res.status(201).json({ status: "success", message: "Lantai berhasil ditambahkan", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE FLOOR ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Lantai pada gedung tersebut sudah ada" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan lantai" });
  }
};

const createRoomType = async (req, res) => {
  try {
    const rawName = String(req.body.name || "").trim();
    if (!rawName) {
      return res.status(400).json({ status: "error", message: "Nama tipe ruangan wajib diisi" });
    }

    const payload = {
      name: rawName.toLowerCase().replace(/\s+/g, "_"),
      description: req.body.description ? String(req.body.description).trim() : null
    };

    const result = await RoomModel.createRoomType(payload);
    res.status(201).json({ status: "success", message: "Tipe ruangan berhasil ditambahkan", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE ROOM TYPE ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Tipe ruangan sudah ada" });
    }
    res.status(500).json({ status: "error", message: "Gagal menambahkan tipe ruangan" });
  }
};

const createRoom = async (req, res) => {
  try {
    const payload = validateRoomPayload(normalizeRoomPayload(req.body));
    const existing = await RoomModel.findByCode(payload.code);
    if (existing) {
      return res.status(409).json({ status: "error", message: "Kode ruangan sudah digunakan" });
    }

    const result = await RoomModel.create(payload);
    res.status(201).json({ status: "success", message: "Ruangan berhasil ditambahkan", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE ROOM ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan ruangan" });
  }
};

const createRoomsBulk = async (req, res) => {
  const connection = await db.getConnection();
  try {
    const rawRooms = Array.isArray(req.body.rooms) ? req.body.rooms : [];
    if (!rawRooms.length) {
      return res.status(400).json({ status: "error", message: "Data ruangan multiple tidak boleh kosong" });
    }

    const rooms = rawRooms.map((room) => validateRoomPayload(normalizeRoomPayload(room)));
    const duplicateInPayload = rooms.find((room, index) => rooms.findIndex((item) => item.code === room.code) !== index);
    if (duplicateInPayload) {
      return res.status(400).json({ status: "error", message: `Kode ruangan ${duplicateInPayload.code} duplikat pada input` });
    }

    await connection.beginTransaction();

    for (const room of rooms) {
      const existing = await RoomModel.findByCode(room.code, connection);
      if (existing) {
        throw Object.assign(new Error(`Kode ruangan ${room.code} sudah digunakan`), { statusCode: 409 });
      }
    }

    const result = await RoomModel.createMany(rooms, connection);
    await connection.commit();

    res.status(201).json({
      status: "success",
      message: `${rooms.length} ruangan berhasil ditambahkan`,
      data: { affected_rows: result.affectedRows }
    });
  } catch (error) {
    await connection.rollback();
    console.error("[CREATE ROOMS BULK ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan ruangan multiple" });
  } finally {
    connection.release();
  }
};

const updateRoom = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID ruangan");
    const existingRoom = await RoomModel.findById(id);
    if (!existingRoom) {
      return res.status(404).json({ status: "error", message: "Ruangan tidak ditemukan" });
    }

    const payload = validateRoomPayload(normalizeRoomPayload(req.body));
    const duplicate = await RoomModel.findByCodeExcludeId(payload.code, id);
    if (duplicate) {
      return res.status(409).json({ status: "error", message: "Kode ruangan sudah digunakan oleh ruangan lain" });
    }

    await RoomModel.update(id, payload);
    res.json({ status: "success", message: "Ruangan berhasil diperbarui" });
  } catch (error) {
    console.error("[UPDATE ROOM ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal memperbarui ruangan" });
  }
};

const deleteRoom = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID ruangan");
    const existingRoom = await RoomModel.findById(id);
    if (!existingRoom) {
      return res.status(404).json({ status: "error", message: "Ruangan tidak ditemukan" });
    }

    await RoomModel.delete(id);
    res.json({ status: "success", message: "Ruangan berhasil dihapus" });
  } catch (error) {
    console.error("[DELETE ROOM ERROR]", error);
    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(409).json({ status: "error", message: "Ruangan tidak dapat dihapus karena masih dipakai oleh data lain" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: "Gagal menghapus ruangan" });
  }
};

const deleteBuilding = async (req, res) => {
  try {
    const result = await RoomModel.deleteBuilding(req.params.id);

    if (!result.affectedRows) {
      return res.status(404).json({
        status: "error",
        message: "Gedung tidak ditemukan"
      });
    }

    res.json({
      status: "success",
      message: "Gedung berhasil dihapus"
    });
  } catch (error) {
    console.error("[DELETE BUILDING ERROR]", error);

    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(400).json({
        status: "error",
        message: "Gedung tidak bisa dihapus karena masih punya lantai/ruangan"
      });
    }

    res.status(500).json({
      status: "error",
      message: "Gagal menghapus gedung"
    });
  }
};

const deleteFloor = async (req, res) => {
  try {
    const result = await RoomModel.deleteFloor(req.params.id);

    if (!result.affectedRows) {
      return res.status(404).json({
        status: "error",
        message: "Lantai tidak ditemukan"
      });
    }

    res.json({
      status: "success",
      message: "Lantai berhasil dihapus"
    });
  } catch (error) {
    console.error("[DELETE FLOOR ERROR]", error);

    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(400).json({
        status: "error",
        message: "Lantai tidak bisa dihapus karena masih punya ruangan"
      });
    }

    res.status(500).json({
      status: "error",
      message: "Gagal menghapus lantai"
    });
  }
};

const deleteRoomType = async (req, res) => {
  try {
    const result = await RoomModel.deleteRoomType(req.params.id);

    if (!result.affectedRows) {
      return res.status(404).json({
        status: "error",
        message: "Tipe ruangan tidak ditemukan"
      });
    }

    res.json({
      status: "success",
      message: "Tipe ruangan berhasil dihapus"
    });
  } catch (error) {
    console.error("[DELETE ROOM TYPE ERROR]", error);

    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(400).json({
        status: "error",
        message: "Tipe ruangan tidak bisa dihapus karena masih dipakai ruangan"
      });
    }

    res.status(500).json({
      status: "error",
      message: "Gagal menghapus tipe ruangan"
    });
  }
};

module.exports = {
  getRooms,
  getRoom,
  getRoomOptions,
  createBuilding,
  createFloor,
  createRoomType,
  createRoom,
  createRoomsBulk,
  updateRoom,
  deleteRoom,
  deleteBuilding,
  deleteFloor,
  deleteRoomType
};