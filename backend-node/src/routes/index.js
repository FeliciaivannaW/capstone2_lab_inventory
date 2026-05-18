const express = require("express");
const router = express.Router();

const healthController = require("../controllers/healthController");
const roleController = require("../controllers/roleController");
const roomController = require("../controllers/roomController");
const laboratoryController = require("../controllers/laboratoryController");
const procurementController = require("../controllers/procurementController");
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

// Procurement routes
router.get(
  "/procurement/drafts",
  authMiddleware,
  roleMiddleware(["administrator", "kepala_laboratorium", "ketua_program_studi", "staf_administrasi"]),
  procurementController.getProcurementDrafts
);

router.post(
  "/procurement/drafts",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.createProcurementDraft
);

router.get(
  "/procurement/drafts/:id",
  authMiddleware,
  roleMiddleware(["administrator", "kepala_laboratorium", "ketua_program_studi", "staf_administrasi"]),
  procurementController.getProcurementDraft
);

router.put(
  "/procurement/drafts/:id",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.updateProcurementDraft
);

router.delete(
  "/procurement/drafts/:id",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.deleteProcurementDraft
);

router.post(
  "/procurement/drafts/:id/items",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.addProcurementItem
);

router.delete(
  "/procurement/drafts/:id/items/:itemId",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.deleteProcurementItem
);

router.post(
  "/procurement/drafts/:draftId/items/:itemId/review",
  authMiddleware,
  roleMiddleware(["ketua_program_studi"]),
  procurementController.reviewProcurementItem
);

router.post(
  "/procurement/drafts/:id/finalize",
  authMiddleware,
  roleMiddleware(["ketua_program_studi"]),
  procurementController.finalizeProcurementDraft
);

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