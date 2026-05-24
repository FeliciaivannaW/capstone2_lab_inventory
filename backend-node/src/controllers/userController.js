const db = require("../config/database");
const bcrypt = require("bcrypt");

const normalizeNullableInt = (value) => {
  if (value === undefined || value === null || value === "") return null;
  const parsed = Number(value);
  return Number.isInteger(parsed) ? parsed : NaN;
};

const getUsers = async (req, res) => {
  try {
    const { role, status, search } = req.query;
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
        u.updated_at
      FROM users u
      JOIN roles r ON u.role_id = r.id
      LEFT JOIN laboratories l ON u.lab_id = l.id
      ${whereClause}
      ORDER BY u.created_at DESC, u.id DESC
    `, params);

    res.json({ status: "success", data: users });
  } catch (error) {
    console.error("[GET USERS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data user" });
  }
};

const getUser = async (req, res) => {
  try {
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
        u.updated_at
      FROM users u
      JOIN roles r ON u.role_id = r.id
      LEFT JOIN laboratories l ON u.lab_id = l.id
      WHERE u.id = ?
      LIMIT 1
    `, [req.params.id]);

    if (!users.length) {
      return res.status(404).json({ status: "error", message: "User tidak ditemukan" });
    }

    res.json({ status: "success", data: users[0] });
  } catch (error) {
    console.error("[GET USER ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil detail user" });
  }
};

const createUser = async (req, res) => {
  try {
    const { name, nrp_nip, email, password, role_id, status = "active" } = req.body;
    const lab_id = normalizeNullableInt(req.body.lab_id);

    if (!name || !email || !password || !role_id) {
      return res.status(400).json({ status: "error", message: "Nama, email, password, dan role wajib diisi" });
    }

    if (!Number.isInteger(Number(role_id)) || Number(role_id) <= 0 || Number.isNaN(lab_id)) {
      return res.status(400).json({ status: "error", message: "Role atau lab tidak valid" });
    }

    if (!["active", "inactive"].includes(status)) {
      return res.status(400).json({ status: "error", message: "Status hanya boleh active atau inactive" });
    }

    const [existing] = await db.query("SELECT id FROM users WHERE email = ? LIMIT 1", [email]);
    if (existing.length) {
      return res.status(409).json({ status: "error", message: "Email sudah digunakan" });
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    const [result] = await db.query(`
      INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password, status)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `, [Number(role_id), lab_id, name, nrp_nip || null, email, hashedPassword, status]);

    res.status(201).json({
      status: "success",
      message: "User berhasil ditambahkan",
      data: { id: result.insertId }
    });
  } catch (error) {
    console.error("[CREATE USER ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal menambahkan user" });
  }
};

const updateUser = async (req, res) => {
  try {
    const { name, nrp_nip, email, password, role_id, status = "active" } = req.body;
    const lab_id = normalizeNullableInt(req.body.lab_id);
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

    const [users] = await db.query("SELECT id FROM users WHERE id = ? LIMIT 1", [userId]);
    if (!users.length) {
      return res.status(404).json({ status: "error", message: "User tidak ditemukan" });
    }

    const [emailTaken] = await db.query("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1", [email, userId]);
    if (emailTaken.length) {
      return res.status(409).json({ status: "error", message: "Email sudah digunakan user lain" });
    }

    if (password && password.trim() !== "") {
      const hashedPassword = await bcrypt.hash(password, 10);
      await db.query(`
        UPDATE users
        SET role_id = ?, lab_id = ?, name = ?, nrp_nip = ?, email = ?, password = ?, status = ?
        WHERE id = ?
      `, [Number(role_id), lab_id, name, nrp_nip || null, email, hashedPassword, status, userId]);
    } else {
      await db.query(`
        UPDATE users
        SET role_id = ?, lab_id = ?, name = ?, nrp_nip = ?, email = ?, status = ?
        WHERE id = ?
      `, [Number(role_id), lab_id, name, nrp_nip || null, email, status, userId]);
    }

    res.json({ status: "success", message: "User berhasil diperbarui" });
  } catch (error) {
    console.error("[UPDATE USER ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal memperbarui user" });
  }
};

const deleteUser = async (req, res) => {
  try {
    const userId = Number(req.params.id);

    if (!Number.isInteger(userId) || userId <= 0) {
      return res.status(400).json({ status: "error", message: "ID user tidak valid" });
    }

    if (req.user?.id === userId) {
      return res.status(400).json({ status: "error", message: "User yang sedang login tidak boleh menghapus akunnya sendiri" });
    }

    const [result] = await db.query("DELETE FROM users WHERE id = ?", [userId]);
    if (result.affectedRows === 0) {
      return res.status(404).json({ status: "error", message: "User tidak ditemukan" });
    }

    res.json({ status: "success", message: "User berhasil dihapus" });
  } catch (error) {
    console.error("[DELETE USER ERROR]", error);
    if (error.code === "ER_ROW_IS_REFERENCED_2") {
      return res.status(409).json({ status: "error", message: "User tidak bisa dihapus karena sudah dipakai pada data lain. Ubah status menjadi inactive saja." });
    }
    res.status(500).json({ status: "error", message: "Gagal menghapus user" });
  }
};

module.exports = {
  getUsers,
  getUser,
  createUser,
  updateUser,
  deleteUser
};