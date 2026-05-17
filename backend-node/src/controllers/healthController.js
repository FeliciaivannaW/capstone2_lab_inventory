const db = require("../config/database");

const checkHealth = async (req, res, next) => {
  try {
    const [rows] = await db.query("SELECT 1 AS database_status");

    res.json({
      status: "success",
      message: "Backend dan database terhubung",
      database: rows[0]
    });
  } catch (error) {
    console.error("[HEALTH CHECK ERROR]", error);
    res.status(503).json({
      status: "error",
      message: "Koneksi database gagal",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  checkHealth
};
