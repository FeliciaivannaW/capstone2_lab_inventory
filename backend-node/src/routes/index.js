const express = require("express");
const router  = express.Router();
const multer  = require("multer");
const path    = require("path");
const fs      = require("fs");

const healthController      = require("../controllers/healthController");
const roleController        = require("../controllers/roleController");
const roomController        = require("../controllers/roomController");
const laboratoryController  = require("../controllers/laboratoryController");
const procurementController = require("../controllers/procurementController");
const authController        = require("../controllers/authController");
const goodsReceiptController = require("../controllers/goodsReceiptController");
const inventoryController   = require("../controllers/inventoryController");
const statisticsController  = require("../controllers/statisticsController");
const uploadController      = require("../controllers/uploadController");

const authMiddleware = require("../middleware/authMiddleware");
const roleMiddleware = require("../middleware/roleMiddleware");

// ============================================================
// MULTER — QR/barcode upload (old endpoint, kept for compat)
// ============================================================
const qrStorage = multer.diskStorage({
  destination: function (req, file, cb) {
    const dir = path.join(__dirname, '../../uploads/qr');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1e9);
    cb(null, 'qr-' + uniqueSuffix + path.extname(file.originalname));
  }
});
const uploadQr = multer({
  storage: qrStorage,
  limits: { fileSize: 2 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (allowed.includes(file.mimetype)) cb(null, true);
    else cb(new Error('Hanya file gambar (JPEG, PNG, WEBP) yang diperbolehkan'));
  }
});

// ============================================================
// MULTER — Asset photo upload (new dedicated endpoint)
// ============================================================
const assetPhotoStorage = multer.diskStorage({
  destination: function (req, file, cb) {
    const dir = path.join(__dirname, '../../uploads/assets');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1e9);
    cb(null, 'asset-' + uniqueSuffix + path.extname(file.originalname));
  }
});
const uploadAsset = multer({
  storage: assetPhotoStorage,
  limits: { fileSize: 2 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (allowed.includes(file.mimetype)) cb(null, true);
    else cb(new Error('Hanya file gambar (JPEG, PNG, WEBP) yang diperbolehkan'));
  }
});

// ─── Public routes ────────────────────────────────────────────────────────────
router.get("/health", healthController.checkHealth);

router.get("/login", (req, res) => {
  res.json({ status: "info", message: "Use POST /api/login with email and password." });
});
router.post("/login",   authController.login);
router.get("/profile",  authMiddleware, authController.profile);

router.get("/roles",       roleController.getRoles);
router.get("/rooms",       roomController.getRooms);
router.get("/laboratories", laboratoryController.getLaboratories);

// ============================================================
// PROCUREMENT DRAFT ROUTES
// ============================================================

// GET list (all roles that can view)
router.get(
  "/procurement/drafts",
  authMiddleware,
  roleMiddleware(["administrator", "kepala_laboratorium", "ketua_program_studi", "staf_administrasi"]),
  procurementController.getProcurementDrafts
);

// POST create
router.post(
  "/procurement/drafts",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.createProcurementDraft
);

// GET single
router.get(
  "/procurement/drafts/:id",
  authMiddleware,
  roleMiddleware(["administrator", "kepala_laboratorium", "ketua_program_studi", "staf_administrasi"]),
  procurementController.getProcurementDraft
);

// PUT update draft info
router.put(
  "/procurement/drafts/:id",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.updateProcurementDraft
);

// PATCH submit draft (draft → submitted)
router.patch(
  "/procurement/drafts/:id/submit",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.submitProcurementDraft
);

// DELETE draft
router.delete(
  "/procurement/drafts/:id",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.deleteProcurementDraft
);

// POST add item to draft
router.post(
  "/procurement/drafts/:id/items",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.addProcurementItem
);

// PATCH edit item in draft
router.patch(
  "/procurement/drafts/:id/items/:itemId",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.updateProcurementItem
);

// DELETE item from draft
router.delete(
  "/procurement/drafts/:id/items/:itemId",
  authMiddleware,
  roleMiddleware(["kepala_laboratorium", "staf_administrasi"]),
  procurementController.deleteProcurementItem
);

// POST review item (Kaprodi only)
router.post(
  "/procurement/drafts/:draftId/items/:itemId/review",
  authMiddleware,
  roleMiddleware(["ketua_program_studi"]),
  procurementController.reviewProcurementItem
);

// POST finalize draft (Kaprodi only)
router.post(
  "/procurement/drafts/:id/finalize",
  authMiddleware,
  roleMiddleware(["ketua_program_studi"]),
  procurementController.finalizeProcurementDraft
);

// ============================================================
// GOODS RECEIPT ROUTES
// ============================================================

// GET pending items (not yet fully received) — grouped by draft
router.get(
  "/goods-receipts/pending",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  goodsReceiptController.getPendingItems
);

// GET receipts by draft
router.get(
  "/goods-receipts/by-draft/:draftId",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  goodsReceiptController.getReceiptsByDraft
);

// GET receipts — optional ?procurement_item_id=:id filter
router.get(
  "/goods-receipts",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  goodsReceiptController.getReceiptsByItem
);

// POST create receipt (triggers inventory_assets / bhp_stocks logic)
router.post(
  "/goods-receipts",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  goodsReceiptController.createGoodsReceipt
);

// ============================================================
// INVENTORY ASSET ROUTES
// ============================================================

// GET all assets (with filters)
router.get(
  "/inventory/assets",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium"]),
  inventoryController.getInventoryAssets
);

// GET single asset detail
router.get(
  "/inventory/assets/:id",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator", "staf_laboratorium"]),
  inventoryController.getInventoryAsset
);

// PATCH update label info (JSON body, no file)
router.patch(
  "/inventory/assets/:id/label",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  inventoryController.updateAssetLabel
);

// PUT update label + optional QR photo upload (multipart, legacy compat)
router.put(
  "/inventory/assets/:id/label",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  inventoryController.updateAssetLabel
);

// POST update label + photo (multipart)
router.post(
  "/inventory/assets/:id/label",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  uploadQr.single('qr_photo'),
  inventoryController.updateAssetLabel
);

// GET asset lifecycle timeline
router.get(
  "/inventory/assets/:id/timeline",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  inventoryController.getAssetTimeline
);

// ============================================================
// UPLOAD ROUTES
// ============================================================

// POST upload asset photo — returns photo_url
router.post(
  "/upload/asset-photo",
  authMiddleware,
  roleMiddleware(["staf_administrasi"]),
  uploadAsset.single('photo'),
  uploadController.uploadAssetPhoto
);

// ============================================================
// STATISTICS ROUTES
// ============================================================
router.get(
  "/statistics/summary",
  authMiddleware,
  roleMiddleware(["staf_administrasi", "administrator"]),
  statisticsController.getSummary
);

// ─── Admin-only test ──────────────────────────────────────────────────────────
router.get(
  "/admin-only",
  authMiddleware,
  roleMiddleware(["administrator"]),
  (req, res) => res.json({ status: "success", message: "Halaman khusus administrator" })
);

module.exports = router;