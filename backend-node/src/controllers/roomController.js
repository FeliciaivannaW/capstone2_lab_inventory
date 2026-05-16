const db = require("../config/database");

const getRooms = async (req, res) => {
  try {
    const [rooms] = await db.query(`
      SELECT 
        rooms.id,
        rooms.code,
        rooms.name,
        room_types.name AS room_type,
        floors.name AS floor_name,
        floors.floor_number,
        buildings.name AS building_name,
        rooms.capacity,
        rooms.description
      FROM rooms
      JOIN room_types ON rooms.room_type_id = room_types.id
      JOIN floors ON rooms.floor_id = floors.id
      JOIN buildings ON floors.building_id = buildings.id
      ORDER BY rooms.code ASC
    `);

    res.json({
      status: "success",
      data: rooms
    });
  } catch (error) {
    res.status(500).json({
      status: "error",
      message: "Failed to get rooms",
      error: error.message
    });
  }
};

module.exports = {
  getRooms
};