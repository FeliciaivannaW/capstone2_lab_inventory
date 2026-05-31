const LaboratoryModel = require("../models/LaboratoryModel");

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

const getLaboratories = async (req, res) => {
  try {
    const laboratories = await LaboratoryModel.findAll();
    res.json({ status: "success", data: laboratories });
  } catch (error) {
    console.error("[GET LABORATORIES ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data laboratorium" });
  }
};

const getLaboratoryOptions = async (req, res) => {
  try {
    const [available_rooms, heads, staff_lab_users, lab_groups] = await Promise.all([
      LaboratoryModel.findAvailableLabRooms(),
      LaboratoryModel.findHeads(),
      LaboratoryModel.findStaffLabUsers(),
      LaboratoryModel.findLabGroups()
    ]);

    res.json({
      status: "success",
      data: {
        available_rooms,
        heads,
        staff_lab_users,
        lab_groups
      }
    });
  } catch (error) {
    console.error("[GET LAB OPTIONS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil opsi laboratorium" });
  }
};

const createLaboratory = async (req, res) => {
  try {
    const payload = {
      room_id: toPositiveInt(req.body.room_id, "Ruangan"),
      head_user_id: toPositiveInt(req.body.head_user_id, "Kepala laboratorium", false),
      name: String(req.body.name || "").trim(),
      code: String(req.body.code || "").trim(),
      description: req.body.description ? String(req.body.description).trim() : null
    };

    if (!payload.name || !payload.code) {
      return res.status(400).json({ status: "error", message: "Nama dan kode laboratorium wajib diisi" });
    }

    const result = await LaboratoryModel.create(payload);
    res.status(201).json({ status: "success", message: "Laboratorium berhasil ditambahkan", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE LAB ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Kode lab atau ruangan sudah dipakai" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan laboratorium" });
  }
};

const updateLaboratory = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID laboratorium");
    const existing = await LaboratoryModel.findById(id);
    if (!existing) {
      return res.status(404).json({ status: "error", message: "Laboratorium tidak ditemukan" });
    }

    const payload = {
      room_id: toPositiveInt(req.body.room_id, "Ruangan"),
      head_user_id: toPositiveInt(req.body.head_user_id, "Kepala laboratorium", false),
      name: String(req.body.name || "").trim(),
      code: String(req.body.code || "").trim(),
      description: req.body.description ? String(req.body.description).trim() : null
    };

    if (!payload.name || !payload.code) {
      return res.status(400).json({ status: "error", message: "Nama dan kode laboratorium wajib diisi" });
    }

    await LaboratoryModel.update(id, payload);
    res.json({ status: "success", message: "Laboratorium berhasil diperbarui" });
  } catch (error) {
    console.error("[UPDATE LAB ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Kode lab atau ruangan sudah dipakai" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal memperbarui laboratorium" });
  }
};

const deleteLaboratory = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID laboratorium");
    const existing = await LaboratoryModel.findById(id);
    if (!existing) {
      return res.status(404).json({ status: "error", message: "Laboratorium tidak ditemukan" });
    }

    await LaboratoryModel.delete(id);
    res.json({ status: "success", message: "Laboratorium berhasil dihapus" });
  } catch (error) {
    console.error("[DELETE LAB ERROR]", error);
    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(409).json({ status: "error", message: "Laboratorium tidak dapat dihapus karena masih dipakai oleh data lain" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: "Gagal menghapus laboratorium" });
  }
};

const getLabGroups = async (req, res) => {
  try {
    const groups = await LaboratoryModel.findLabGroups();
    res.json({ status: "success", data: groups });
  } catch (error) {
    console.error("[GET LAB GROUPS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data grup lab" });
  }
};

const createLabGroup = async (req, res) => {
  try {
    const payload = {
      laboratory_id: toPositiveInt(req.body.laboratory_id, "Laboratorium"),
      name: String(req.body.name || "").trim(),
      description: req.body.description ? String(req.body.description).trim() : null
    };

    if (!payload.name) {
      return res.status(400).json({ status: "error", message: "Nama grup wajib diisi" });
    }

    const result = await LaboratoryModel.createGroup(payload);
    res.status(201).json({ status: "success", message: "Grup lab berhasil dibuat", data: { id: result.insertId } });
  } catch (error) {
    console.error("[CREATE LAB GROUP ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Nama grup sudah ada pada lab tersebut" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal membuat grup lab" });
  }
};

const updateLabGroup = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID grup lab");
    const existing = await LaboratoryModel.findLabGroups();
    const isExist = existing.some(g => g.id === id);
    if (!isExist) {
      return res.status(404).json({ status: "error", message: "Grup lab tidak ditemukan" });
    }

    const payload = {
      laboratory_id: toPositiveInt(req.body.laboratory_id, "Laboratorium"),
      name: String(req.body.name || "").trim(),
      description: req.body.description ? String(req.body.description).trim() : null
    };

    if (!payload.name) {
      return res.status(400).json({ status: "error", message: "Nama grup wajib diisi" });
    }

    await LaboratoryModel.updateGroup(id, payload);
    res.json({ status: "success", message: "Grup lab berhasil diperbarui" });
  } catch (error) {
    console.error("[UPDATE LAB GROUP ERROR]", error);
    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ status: "error", message: "Nama grup sudah ada pada lab tersebut" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal memperbarui grup lab" });
  }
};

const deleteLabGroup = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID grup lab");
    await LaboratoryModel.deleteGroup(id);
    res.json({ status: "success", message: "Grup lab berhasil dihapus" });
  } catch (error) {
    console.error("[DELETE LAB GROUP ERROR]", error);
    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(409).json({ status: "error", message: "Grup lab tidak dapat dihapus karena masih memiliki anggota user atau ruangan" });
    }
    res.status(error.statusCode || 500).json({ status: "error", message: "Gagal menghapus grup lab" });
  }
};

const addUserToGroup = async (req, res) => {
  try {
    const groupId = toPositiveInt(req.params.groupId, "ID grup");
    const userId = toPositiveInt(req.body.user_id, "User");
    const roleInGroup = req.body.role_in_group === "kepala_lab" ? "kepala_lab" : "staf_lab";

    await LaboratoryModel.addUserToGroup({ group_id: groupId, user_id: userId, role_in_group: roleInGroup });
    res.json({ status: "success", message: "User berhasil dimasukkan ke grup lab" });
  } catch (error) {
    console.error("[ADD USER TO LAB GROUP ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan user ke grup lab" });
  }
};

const addRoomToGroup = async (req, res) => {
  try {
    const groupId = toPositiveInt(req.params.groupId, "ID grup");
    const roomId = toPositiveInt(req.body.room_id, "Ruangan");

    await LaboratoryModel.addRoomToGroup({ group_id: groupId, room_id: roomId });
    res.json({ status: "success", message: "Ruangan berhasil dimasukkan ke grup lab" });
  } catch (error) {
    console.error("[ADD ROOM TO LAB GROUP ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan ruangan ke grup lab" });
  }
};

const getLabGroupDetails = async (req, res) => {
  try {
    const id = toPositiveInt(req.params.id, "ID grup lab");
    const group = await LaboratoryModel.findGroupById(id);
    if (!group) {
      return res.status(404).json({ status: "error", message: "Grup lab tidak ditemukan" });
    }

    const [users, rooms] = await Promise.all([
      LaboratoryModel.findGroupUsers(id),
      LaboratoryModel.findGroupRooms(id)
    ]);

    res.json({
      status: "success",
      data: { ...group, users, rooms }
    });
  } catch (error) {
    console.error("[GET LAB GROUP DETAILS ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: "Gagal mengambil detail grup lab" });
  }
};

const removeUserFromGroup = async (req, res) => {
  try {
    const groupId = toPositiveInt(req.params.groupId, "ID grup");
    const userId = toPositiveInt(req.params.userId, "ID user");

    await LaboratoryModel.removeUserFromGroup(groupId, userId);
    res.json({ status: "success", message: "User berhasil dihapus dari grup lab" });
  } catch (error) {
    console.error("[REMOVE USER FROM GROUP ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: "Gagal menghapus user dari grup lab" });
  }
};

const removeRoomFromGroup = async (req, res) => {
  try {
    const groupId = toPositiveInt(req.params.groupId, "ID grup");
    const roomId = toPositiveInt(req.params.roomId, "ID ruangan");

    await LaboratoryModel.removeRoomFromGroup(groupId, roomId);
    res.json({ status: "success", message: "Ruangan berhasil dihapus dari grup lab" });
  } catch (error) {
    console.error("[REMOVE ROOM FROM GROUP ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: "Gagal menghapus ruangan dari grup lab" });
  }
};

module.exports = {
  getLaboratories,
  getLaboratoryOptions,
  createLaboratory,
  updateLaboratory,
  deleteLaboratory,
  getLabGroups,
  createLabGroup,
  updateLabGroup,
  deleteLabGroup,
  addUserToGroup,
  addRoomToGroup,
  getLabGroupDetails,
  removeUserFromGroup,
  removeRoomFromGroup
};