const express = require("express");
const router = express.Router();

const healthController = require("../controllers/healthController");
const roleController = require("../controllers/roleController");
const roomController = require("../controllers/roomController");
const laboratoryController = require("../controllers/laboratoryController");
const authController = require("../controllers/authController");

const authMiddleware = require("../middleware/authMiddleware");
const roleMiddleware = require("../middleware/roleMiddleware");

router.get("/health", healthController.checkHealth);

router.get("/login", (req, res) => {
  res.json({
    status: "info",
    message: "Login endpoint uses POST method. Please use POST /api/login with email and password."
  });
});

router.post("/login", authController.login);
router.get("/profile", authMiddleware, authController.profile);

router.get("/roles", roleController.getRoles);
router.get("/rooms", roomController.getRooms);
router.get("/laboratories", laboratoryController.getLaboratories);

router.get(
  "/admin-only",
  authMiddleware,
  roleMiddleware(["administrator"]),
  (req, res) => {
    res.json({
      status: "success",
      message: "Ini halaman khusus administrator"
    });
  }
);

module.exports = router;