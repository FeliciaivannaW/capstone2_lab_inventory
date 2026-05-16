const db = require("../config/database");

const checkHealth = async (req, res) => {
  try {
    const [rows] = await db.query("SELECT 1 AS database_status");

    res.json({
      status: "success",
      message: "Backend and database connected",
      database: rows[0]
    });
  } catch (error) {
    res.status(500).json({
      status: "error",
      message: "Database connection failed",
      error: error.message
    });
  }
};

module.exports = {
  checkHealth
};
