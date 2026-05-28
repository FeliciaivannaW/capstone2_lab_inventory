const db = require("../config/database");
const UserModel = require("../models/UserModel");
const bcrypt = require("bcrypt");

const normalizeNullableInt = (value) => {
  if (value === undefined || value === null || value === "") return null;
  const parsed = Number(value);
  return Number.isInteger(parsed) ? parsed : NaN;
};

const normalizeIdArray = (value) => {
  if (!value) return [];

  const raw = Array.isArray(value) ? value : [value];

  return [...new Set(
    raw
      .flatMap((item) => String(item).split(","))
      .map((item) => Number(item))
      .filter((item) => Number.isInteger(item) && item > 0)
  )];
};

const getUsers = async (req, res) => {
  try {
    const { role, status, search } = req.query;
    const users = await UserModel.findAll({ role, status, search });
    res.json({ status: "success", data: users });
  } catch (error) {
    console.error("[GET USERS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data user" });
  }
};

const getUser = async (req, res) => {
  try {
    const user = await UserModel.findById(req.params.id);

    if (!user) {
      return res.status(404).json({ status: "error", message: "User tidak ditemukan" });
    }

    res.json({ status: "success", data: user });
  } catch (error) {
    console.error("[GET USER ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil detail user" });
  }
};

const createUser = async (req, res) => {
  const connection = await db.getConnection();

  try {
    const { name, nrp_nip, email, password, role_id, status = "active" } = req.body;
    const lab_id = normalizeNullableInt(req.body.lab_id);
    const labGroupIds = normalizeIdArray(req.body.lab_group_ids);

    if (!name || !email || !password || !role_id) {
      return res.status(400).json({ status: "error", message: "Nama, email, password, dan role wajib diisi" });
    }

    if (!Number.isInteger(Number(role_id)) || Number(role_id) <= 0 || Number.isNaN(lab_id)) {
      return res.status(400).json({ status: "error", message: "Role atau lab tidak valid" });
    }

    if (!["active", "inactive"].includes(status)) {
      return res.status(400).json({ status: "error", message: "Status hanya boleh active atau inactive" });
    }

    const existing = await UserModel.findByEmail(email);
    if (existing) {
      return res.status(409).json({ status: "error", message: "Email sudah digunakan" });
    }

    const hashedPassword = await bcrypt.hash(password, 10);

    await connection.beginTransaction();

    const result = await UserModel.create({
      role_id: Number(role_id),
      lab_id,
      name,
      nrp_nip,
      email,
      password: hashedPassword,
      status
    }, connection);

    await UserModel.syncLabGroups(result.insertId, labGroupIds, connection);

    await connection.commit();

    res.status(201).json({
      status: "success",
      message: "User berhasil ditambahkan",
      data: { id: result.insertId }
    });
  } catch (error) {
    try {
      await connection.rollback();
    } catch (_) {}

    console.error("[CREATE USER ERROR]", error);

    if (error.code === "ER_NO_REFERENCED_ROW_2") {
      return res.status(400).json({
        status: "error",
        message: "Role, lab, atau grup lab tidak valid"
      });
    }

    res.status(500).json({ status: "error", message: "Gagal menambahkan user" });
  } finally {
    connection.release();
  }
};

const updateUser = async (req, res) => {
  const connection = await db.getConnection();

  try {
    const { name, nrp_nip, email, password, role_id, status = "active" } = req.body;
    const lab_id = normalizeNullableInt(req.body.lab_id);
    const labGroupIds = normalizeIdArray(req.body.lab_group_ids);
    const userId = Number(req.params.id);

    if (!Number.isInteger(userId) || userId <= 0) {
      return res.status(400).json({ status: "error", message: "ID user tidak valid" });
    }

    if (!name || !email || !role_id) {
      return res.status(400).json({ status: "error", message: "Nama, email, dan role wajib diisi" });
    }

    if (!Number.isInteger(Number(role_id)) || Number(role_id) <= 0 || Number.isNaN(lab_id)) {
      return res.status(400).json({ status: "error", message: "Role atau lab tidak valid" });
    }

    if (!["active", "inactive"].includes(status)) {
      return res.status(400).json({ status: "error", message: "Status hanya boleh active atau inactive" });
    }

    const user = await UserModel.findById(userId);
    if (!user) {
      return res.status(404).json({ status: "error", message: "User tidak ditemukan" });
    }

    const emailTaken = await UserModel.findByEmailExcludeId(email, userId);
    if (emailTaken) {
      return res.status(409).json({ status: "error", message: "Email sudah digunakan user lain" });
    }

    let hashedPassword = null;
    if (password && password.trim() !== "") {
      hashedPassword = await bcrypt.hash(password, 10);
    }

    await connection.beginTransaction();

    await UserModel.update(userId, {
      role_id: Number(role_id),
      lab_id,
      name,
      nrp_nip,
      email,
      password: hashedPassword,
      status
    }, connection);

    await UserModel.syncLabGroups(userId, labGroupIds, connection);

    await connection.commit();

    res.json({ status: "success", message: "User berhasil diperbarui" });
  } catch (error) {
    try {
      await connection.rollback();
    } catch (_) {}

    console.error("[UPDATE USER ERROR]", error);

    if (error.code === "ER_NO_REFERENCED_ROW_2") {
      return res.status(400).json({
        status: "error",
        message: "Role, lab, atau grup lab tidak valid"
      });
    }

    res.status(500).json({ status: "error", message: "Gagal memperbarui user" });
  } finally {
    connection.release();
  }
};

const deleteUser = async (req, res) => {
  const connection = await db.getConnection();

  try {
    const userId = Number(req.params.id);

    if (!Number.isInteger(userId) || userId <= 0) {
      return res.status(400).json({ status: "error", message: "ID user tidak valid" });
    }

    if (req.user?.id === userId) {
      return res.status(400).json({ status: "error", message: "User yang sedang login tidak boleh menghapus akunnya sendiri" });
    }

    await connection.beginTransaction();

    await UserModel.deleteLabGroupAccess(userId, connection);
    const result = await UserModel.delete(userId, connection);

    if (result.affectedRows === 0) {
      await connection.rollback();
      return res.status(404).json({ status: "error", message: "User tidak ditemukan" });
    }

    await connection.commit();

    res.json({ status: "success", message: "User berhasil dihapus" });
  } catch (error) {
    try {
      await connection.rollback();
    } catch (_) {}

    console.error("[DELETE USER ERROR]", error);

    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(409).json({
        status: "error",
        message: "User tidak bisa dihapus karena masih dipakai sebagai penanggung jawab atau data lain"
      });
    }

    res.status(500).json({ status: "error", message: "Gagal menghapus user" });
  } finally {
    connection.release();
  }
};

module.exports = {
  getUsers,
  getUser,
  createUser,
  updateUser,
  deleteUser
};