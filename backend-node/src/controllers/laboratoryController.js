const db = require("../config/database");

const getLaboratories = async (req, res) => {
  try {
    const [laboratories] = await db.query(`
      SELECT
        laboratories.id,
        laboratories.code,
        laboratories.name,
        rooms.code AS room_code,
        rooms.name AS room_name,
        floors.name AS floor_name,
        buildings.name AS building_name,
        users.name AS head_name
      FROM laboratories
      JOIN rooms ON laboratories.room_id = rooms.id
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      LEFT JOIN users ON laboratories.head_user_id = users.id
      ORDER BY laboratories.id ASC
    `);

    res.json({
      status: "success",
      data: laboratories
    });
  } catch (error) {
    res.status(500).json({
      status: "error",
      message: "Failed to get laboratories",
      error: error.message
    });
  }
};

module.exports = {
  getLaboratories
};