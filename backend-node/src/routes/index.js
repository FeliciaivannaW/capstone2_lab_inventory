const express = require("express");
const router = express.Router();
const multer = require("multer");
const path = require("path");

const healthController = require("../controllers/healthController");
const roleController = require("../controllers/roleController");
const roomController = require("../controllers/roomController");
const laboratoryController = require("../controllers/laboratoryController");
const procurementController = require("../controllers/procurementController");
const authController = require("../controllers/authController");
const goodsReceiptController = require("../controllers/goodsReceiptController");
const inventoryController = require("../controllers/inventoryController");
const statisticsController = require("../controllers/statisticsController");

const authMiddleware = require("../middleware/authMiddleware");
const roleMiddleware = require("../middleware/roleMiddleware");

// Multer configuration for file uploads
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    const uploadDir = path.join(__dirname, '../../uploads/qr');
    const fs = require('fs');
    if (!fs.existsSync(uploadDir)) {
      fs.mkdirSync(uploadDir, { recursive: true });
    }
    cb(null, uploadDir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, 'qr-' + uniqueSuffix + path.extname(file.originalname));
  }
});

const upload = multer({
  storage: storage,
  limits: { fileSize: 2 * 1024 * 1024 }, // 2MB max
  fileFilter: (req, file, cb) => {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Hanya file gambar (JPEG, PNG, WEBP) yang diperbolehkan'));
    }
  }
});

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

// ============================================================
// GOODS RECEIPT ROUTES (Staf Administrasi - Fitur 2)
// ============================================================
router.get(
  "/goods-receipts/by-draft/:draftId",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  goodsReceiptController.getReceiptsByDraft
);

router.post(
  "/goods-receipts",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  goodsReceiptController.createGoodsReceipt
);

// ============================================================
// INVENTORY ASSET ROUTES (Staf Administrasi - Fitur 3 & 5)
// ============================================================
router.get(
  "/inventory/assets",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium"]),
  inventoryController.getInventoryAssets
);

router.get(
  "/inventory/assets/:id",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium"]),
  inventoryController.getInventoryAsset
);

router.put(
  "/inventory/assets/:id/label",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  inventoryController.updateAssetLabel
);

router.post(
  "/inventory/assets/:id/label",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  upload.single('qr_photo'),
  inventoryController.updateAssetLabel
);

router.get(
  "/inventory/assets/:id/timeline",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  inventoryController.getAssetTimeline
);

// ============================================================
// STATISTICS ROUTES (Staf Administrasi - Fitur 4)
// ============================================================
router.get(
  "/statistics/summary",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  statisticsController.getSummary
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