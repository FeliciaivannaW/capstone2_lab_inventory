const db = require("../config/database");

const getRoles = async (req, res) => {
  try {
    const [roles] = await db.query("SELECT * FROM roles ORDER BY id ASC");

    res.json({
      status: "success",
      data: roles
    });
  } catch (error) {
    res.status(500).json({
      status: "error",
      message: "Failed to get roles",
      error: error.message
    });
  }
};

module.exports = {
  getRoles
};
