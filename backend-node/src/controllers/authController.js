const UserModel = require("../models/UserModel");
const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");

const login = async (req, res, next) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        status: "error",
        message: "Email dan password wajib diisi"
      });
    }

    if (!process.env.JWT_SECRET) {
      throw new Error("JWT_SECRET tidak dikonfigurasi di environment variables");
    }

    const user = await UserModel.findByEmailForAuth(email);

    if (!user) {
      return res.status(401).json({
        status: "error",
        message: "Email atau password salah"
      });
    }

    if (user.status !== "active") {
      return res.status(403).json({
        status: "error",
        message: "Akun Anda telah dinonaktifkan."
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
        role: user.role,
        lab_id: user.lab_id
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
    console.error("[AUTH ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Login gagal",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const profile = async (req, res, next) => {
  try {
    if (!req.user || !req.user.id) {
      return res.status(401).json({
        status: "error",
        message: "User tidak terautentikasi"
      });
    }

    const user = await UserModel.findByIdForAuth(req.user.id);

    if (!user) {
      return res.status(404).json({
        status: "error",
        message: "User tidak ditemukan"
      });
    }

    res.json({
      status: "success",
      data: user
    });
  } catch (error) {
    console.error("[PROFILE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil profile",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  login,
  profile
};