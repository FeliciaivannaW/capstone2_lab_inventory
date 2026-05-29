const RoleModel = require("../models/RoleModel");

const getRoles = async (req, res, next) => {
  try {
    const roles = await RoleModel.findAll();

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
