const db = require("../config/database");
const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");

const login = async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        status: "error",
        message: "Email dan password wajib diisi"
      });
    }

    const [users] = await db.query(`
      SELECT 
        users.id,
        users.name,
        users.email,
        users.password,
        users.status,
        users.lab_id,
        roles.name AS role,
        laboratories.name AS laboratory_name
      FROM users
      JOIN roles ON users.role_id = roles.id
      LEFT JOIN laboratories ON users.lab_id = laboratories.id
      WHERE users.email = ?
      LIMIT 1
    `, [email]);

    if (users.length === 0) {
      return res.status(401).json({
        status: "error",
        message: "Email atau password salah"
      });
    }

    const user = users[0];

    if (user.status !== "active") {
      return res.status(403).json({
        status: "error",
        message: "Akun tidak aktif"
      });
    }

    let passwordValid = false;

    if (user.password.startsWith("$2b$") || user.password.startsWith("$2a$")) {
      passwordValid = await bcrypt.compare(password, user.password);
    } else {
      passwordValid = password === user.password;
    }

    if (!passwordValid) {
      return res.status(401).json({
        status: "error",
        message: "Email atau password salah"
      });
    }

    const token = jwt.sign(
      {
        id: user.id,
        email: user.email,
        role: user.role
      },
      process.env.JWT_SECRET,
      { expiresIn: "1d" }
    );

    delete user.password;

    res.json({
      status: "success",
      message: "Login berhasil",
      token,
      user
    });
  } catch (error) {
    res.status(500).json({
      status: "error",
      message: "Login gagal",
      error: error.sqlMessage || error.message
    });
  }
};

const profile = async (req, res) => {
  try {
    const [users] = await db.query(`
      SELECT 
        users.id,
        users.name,
        users.email,
        users.status,
        roles.name AS role,
        laboratories.name AS laboratory_name
      FROM users
      JOIN roles ON users.role_id = roles.id
      LEFT JOIN laboratories ON users.lab_id = laboratories.id
      WHERE users.id = ?
      LIMIT 1
    `, [req.user.id]);

    res.json({
      status: "success",
      data: users[0]
    });
  } catch (error) {
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil profile",
      error: error.sqlMessage || error.message
    });
  }
};

module.exports = {
  login,
  profile
};