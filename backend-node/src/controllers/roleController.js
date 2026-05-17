const db = require("../config/database");

const getRoles = async (req, res, next) => {
  try {
    const [roles] = await db.query("SELECT * FROM roles ORDER BY id ASC");

    res.json({
      status: "success",
      data: roles
    });
  } catch (error) {
    console.error("[ROLES ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data roles",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  getRoles
};
