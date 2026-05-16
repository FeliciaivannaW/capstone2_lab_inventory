const express = require("express");
const router = express.Router();

const healthController = require("../controllers/healthController");
const roleController = require("../controllers/roleController");
const roomController = require("../controllers/roomController");
const laboratoryController = require("../controllers/laboratoryController");

router.get("/health", healthController.checkHealth);
router.get("/roles", roleController.getRoles);
router.get("/rooms", roomController.getRooms);
router.get("/laboratories", laboratoryController.getLaboratories);

module.exports = router;