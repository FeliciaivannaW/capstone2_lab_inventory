const express = require("express");
const router  = express.Router();
const multer  = require("multer");
const path    = require("path");
const fs      = require("fs");

const healthController       = require("../controllers/healthController");
const roleController         = require("../controllers/roleController");
const roomController         = require("../controllers/roomController");
const userController         = require("../controllers/userController");
const bhpController          = require("../controllers/bhpController");
const maintenanceController  = require("../controllers/maintenanceController");
const laboratoryController   = require("../controllers/laboratoryController");
const procurementController  = require("../controllers/procurementController");
const authController         = require("../controllers/authController");
const goodsReceiptController = require("../controllers/goodsReceiptController");
const inventoryController    = require("../controllers/inventoryController");
const statisticsController   = require("../controllers/statisticsController");
const uploadController       = require("../controllers/uploadController");
const adminOverviewController = require("../controllers/adminOverviewController");

const authMiddleware = require("../middleware/authMiddleware");
const roleMiddleware = require("../middleware/roleMiddleware");

// ============================================================
// MULTER — QR/barcode upload
// ============================================================
const qrStorage = multer.diskStorage({
  destination: function (req, file, cb) {
    const dir = path.join(__dirname, "../../uploads/qr");
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + "-" + Math.round(Math.random() * 1e9);
    cb(null, "qr-" + uniqueSuffix + path.extname(file.originalname));
  }
});
const uploadQr = multer({
  storage: qrStorage,
  limits: { fileSize: 2 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    const allowed = ["image/jpeg", "image/png", "image/jpg", "image/webp"];
    if (allowed.includes(file.mimetype)) cb(null, true);
    else cb(new Error("Hanya file gambar (JPEG, PNG, WEBP) yang diperbolehkan"));
  }
});

// ============================================================
// MULTER — Multiple upload (QR & Asset photo) for Label
// ============================================================
const labelMultiStorage = multer.diskStorage({
  destination: function (req, file, cb) {
    const isAsset = file.fieldname === 'asset_photo';
    const dir = path.join(__dirname, isAsset ? "../../uploads/assets" : "../../uploads/qr");
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: function (req, file, cb) {
    const prefix = file.fieldname === 'asset_photo' ? "asset-" : "qr-";
    const uniqueSuffix = Date.now() + "-" + Math.round(Math.random() * 1e9);
    cb(null, prefix + uniqueSuffix + path.extname(file.originalname));
  }
});
const uploadLabelMulti = multer({
  storage: labelMultiStorage,
  limits: { fileSize: 2 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    const allowed = ["image/jpeg", "image/png", "image/jpg", "image/webp"];
    if (allowed.includes(file.mimetype)) cb(null, true);
    else cb(new Error("Hanya file gambar (JPEG, PNG, WEBP) yang diperbolehkan"));
  }
});

// ============================================================
// MULTER — Asset photo upload
// ============================================================
const assetPhotoStorage = multer.diskStorage({
  destination: function (req, file, cb) {
    const dir = path.join(__dirname, "../../uploads/assets");
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + "-" + Math.round(Math.random() * 1e9);
    cb(null, "asset-" + uniqueSuffix + path.extname(file.originalname));
  }
});
const uploadAsset = multer({
  storage: assetPhotoStorage,
  limits: { fileSize: 2 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    const allowed = ["image/jpeg", "image/png", "image/jpg", "image/webp"];
    if (allowed.includes(file.mimetype)) cb(null, true);
    else cb(new Error("Hanya file gambar (JPEG, PNG, WEBP) yang diperbolehkan"));
  }
});

// ============================================================
// PUBLIC + AUTH
// ============================================================
router.get("/health", healthController.checkHealth);
router.get("/login", (req, res) => {
  res.json({ status: "info", message: "Use POST /api/login with email and password." });
});
router.post("/login", authController.login);
router.get("/profile", authMiddleware, authController.profile);
router.get("/me/lab-access", authMiddleware, userController.getMyLabAccess);

router.get("/roles", roleController.getRoles);
router.get("/rooms", roomController.getRooms);
router.get("/laboratories", laboratoryController.getLaboratories);

// ============================================================
// ADMIN OVERVIEW — Admin preview saja, bukan kelola BHP
// ============================================================
router.get(
  "/admin/overview",
  authMiddleware,
  roleMiddleware(["administrator"]),
  adminOverviewController.getAdminOverview
);

// ============================================================
// USER MANAGEMENT ROUTES (Administrator)
// ============================================================
router.get("/users", authMiddleware, roleMiddleware(["administrator"]), userController.getUsers);
router.get("/users/:id", authMiddleware, roleMiddleware(["administrator"]), userController.getUser);
router.post("/users", authMiddleware, roleMiddleware(["administrator"]), userController.createUser);
router.put("/users/:id", authMiddleware, roleMiddleware(["administrator"]), userController.updateUser);
router.delete("/users/:id", authMiddleware, roleMiddleware(["administrator"]), userController.deleteUser);

// ============================================================
// ROOM MANAGEMENT ROUTES (Administrator)
// ============================================================
router.post("/buildings", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.createBuilding);
router.post("/floors", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.createFloor);
router.post("/room-types", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.createRoomType);
router.put("/buildings/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.updateBuilding);
router.put("/floors/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.updateFloor);
router.put("/room-types/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.updateRoomType);
router.get("/rooms/options", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.getRoomOptions);
router.post("/rooms/bulk", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.createRoomsBulk);
router.get("/rooms/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.getRoom);
router.post("/rooms", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.createRoom);
router.put("/rooms/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.updateRoom);
router.delete("/rooms/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.deleteRoom);
router.delete("/buildings/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.deleteBuilding);
router.delete("/floors/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.deleteFloor);
router.delete("/room-types/:id", authMiddleware, roleMiddleware(["administrator", "staf_laboratorium"]), roomController.deleteRoomType);

// ============================================================
// LABORATORY + LAB GROUP MANAGEMENT ROUTES (Administrator)
// ============================================================
router.get("/laboratories/options", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.getLaboratoryOptions);
router.post("/laboratories", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.createLaboratory);
router.put("/laboratories/:id", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.updateLaboratory);
router.delete("/laboratories/:id", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.deleteLaboratory);

router.get("/lab-groups", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.getLabGroups);
router.post("/lab-groups", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.createLabGroup);
router.put("/lab-groups/:id", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.updateLabGroup);
router.delete("/lab-groups/:id", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.deleteLabGroup);
router.post("/lab-groups/:groupId/users", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.addUserToGroup);
router.post("/lab-groups/:groupId/rooms", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.addRoomToGroup);
router.get("/lab-groups/:id/details", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.getLabGroupDetails);
router.delete("/lab-groups/:groupId/users/:userId", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.removeUserFromGroup);
router.delete("/lab-groups/:groupId/rooms/:roomId", authMiddleware, roleMiddleware(["administrator"]), laboratoryController.removeRoomFromGroup);

// ============================================================
// BHP STOCK ROUTES (Staf Laboratorium only)
// ============================================================
router.get("/bhp/catalog-readonly", authMiddleware, roleMiddleware(["administrator", "staf_administrasi", "staf_laboratorium", "kepala_laboratorium", "ketua_program_studi"]), bhpController.getBhpCatalogReadonly);
router.get("/bhp/catalogs", authMiddleware, roleMiddleware(["staf_laboratorium"]), bhpController.getBhpCatalogs);
router.get("/bhp/stocks", authMiddleware, roleMiddleware(["staf_laboratorium"]), bhpController.getStocks);
router.post("/bhp/stocks", authMiddleware, roleMiddleware(["staf_laboratorium"]), bhpController.createStock);
router.put("/bhp/stocks/:id", authMiddleware, roleMiddleware(["staf_laboratorium"]), bhpController.updateStock);
router.post("/bhp/stocks/:id/movements", authMiddleware, roleMiddleware(["staf_laboratorium"]), bhpController.adjustStock);
router.get("/bhp/stocks/:id/movements", authMiddleware, roleMiddleware(["staf_laboratorium"]), bhpController.getStockMovements);

// ============================================================
// MAINTENANCE ROUTES (Staf Laboratorium only)
// ============================================================
router.get("/maintenance/logs", authMiddleware, roleMiddleware(["staf_laboratorium"]), maintenanceController.getMaintenanceLogs);
router.post("/maintenance/logs", authMiddleware, roleMiddleware(["staf_laboratorium"]), maintenanceController.createMaintenanceLog);
router.put("/maintenance/logs/:id", authMiddleware, roleMiddleware(["staf_laboratorium"]), maintenanceController.updateMaintenanceLog);
router.delete("/maintenance/logs/:id", authMiddleware, roleMiddleware(["staf_laboratorium"]), maintenanceController.deleteMaintenanceLog);

// ============================================================
// PROCUREMENT DRAFT ROUTES
// ============================================================
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
router.patch(
  "/procurement/drafts/:id/submit",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.submitProcurementDraft
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
router.put(
  "/procurement/drafts/:id/items/sync",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.syncProcurementItems
);
router.patch(
  "/procurement/drafts/:id/items/:itemId",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.updateProcurementItem
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
router.post(
  "/procurement/drafts/:id/return",
  authMiddleware,
  roleMiddleware(["ketua_program_studi"]),
  procurementController.returnProcurementDraft
);

// ============================================================
// GOODS RECEIPT ROUTES
// ============================================================
router.get("/goods-receipts/pending", authMiddleware, roleMiddleware(["staf_administrasi", "administrator"]), goodsReceiptController.getPendingItems);
router.get("/goods-receipts/by-draft/:draftId", authMiddleware, roleMiddleware(["staf_administrasi", "administrator"]), goodsReceiptController.getReceiptsByDraft);
router.get("/goods-receipts", authMiddleware, roleMiddleware(["staf_administrasi", "administrator"]), goodsReceiptController.getReceiptsByItem);
router.post("/goods-receipts", authMiddleware, roleMiddleware(["staf_administrasi"]), goodsReceiptController.createGoodsReceipt);

// ============================================================
// INVENTORY ASSET ROUTES
// ============================================================
router.get("/inventory/label-check", authMiddleware, roleMiddleware(["staf_administrasi"]), inventoryController.checkLabelAvailability);
router.get("/inventory/assets", authMiddleware, roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium", "kepala_laboratorium", "ketua_program_studi"]), inventoryController.getInventoryAssets);
router.get("/inventory/batches", authMiddleware, roleMiddleware(["staf_administrasi", "administrator"]), inventoryController.getInventoryBatches);
router.get("/inventory/condition-history", authMiddleware, roleMiddleware(["staf_laboratorium"]), inventoryController.getConditionHistory);
router.patch("/inventory/assets/:id/condition", authMiddleware, roleMiddleware(["staf_laboratorium"]), inventoryController.updateAssetCondition);
router.put("/inventory/assets/:id/condition", authMiddleware, roleMiddleware(["staf_laboratorium"]), inventoryController.updateAssetCondition);
router.get("/inventory/assets/:id", authMiddleware, roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium"]), inventoryController.getInventoryAsset);
router.patch("/inventory/assets/:id/label", authMiddleware, roleMiddleware(["staf_administrasi"]), inventoryController.updateAssetLabel);
router.put("/inventory/assets/:id/label", authMiddleware, roleMiddleware(["staf_administrasi"]), inventoryController.updateAssetLabel);
router.post("/inventory/assets/:id/label", authMiddleware, roleMiddleware(["staf_administrasi"]), uploadLabelMulti.fields([{ name: "qr_photo", maxCount: 1 }, { name: "asset_photo", maxCount: 1 }]), inventoryController.updateAssetLabel);
router.post("/inventory/batches/:id/label-all", authMiddleware, roleMiddleware(["staf_administrasi"]), inventoryController.labelAllAssets);
router.get("/inventory/next-label", authMiddleware, roleMiddleware(["staf_administrasi"]), inventoryController.getNextLabel);
router.get("/inventory/assets/:id/timeline", authMiddleware, roleMiddleware(["staf_administrasi", "administrator"]), inventoryController.getAssetTimeline);

// ============================================================
// UPLOAD ROUTES
// ============================================================
router.post("/upload/asset-photo", authMiddleware, roleMiddleware(["staf_administrasi"]), uploadAsset.single("photo"), uploadController.uploadAssetPhoto);

// ============================================================
// STATISTICS ROUTES
// ============================================================
router.get("/statistics/summary", authMiddleware, roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium"]), statisticsController.getSummary);

router.get("/admin-only", authMiddleware, roleMiddleware(["administrator"]), (req, res) => {
  res.json({ status: "success", message: "Halaman khusus administrator" });
});

module.exports = router;