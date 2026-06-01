const LabAccessModel = require("../models/LabAccessModel");
const InventoryModel = require("../models/InventoryModel");
const db   = require("../config/database");
const path = require("path");
const fs   = require("fs");
const QRCode = require("qrcode");

const getAccessibleRoomIdsForUser = async (user) => {
  if (user?.role !== "staf_laboratorium") {
    return null;
  }

  const roomIds = await LabAccessModel.findAccessibleRoomIds(user.id);
  return roomIds.map((id) => Number(id));
};

const checkAssetRoomAccess = async (user, asset, assetId) => {
  if (user?.role !== "staf_laboratorium") {
    return true;
  }

  const accessibleRoomIds = await getAccessibleRoomIdsForUser(user);

  let roomId = asset?.room_id ? Number(asset.room_id) : null;

  if (!roomId) {
    const assetDetail = await InventoryModel.findById(assetId);
    roomId = assetDetail?.room_id ? Number(assetDetail.room_id) : null;
  }

  if (!roomId) {
    return false;
  }

  return accessibleRoomIds.includes(roomId);
};

const getInventoryAssets = async (req, res) => {
  try {
    const { search, status, condition, label_status, lab_id, receipt_id, sort } = req.query;
    const roomIds = await getAccessibleRoomIdsForUser(req.user);

    if (req.user?.role === "staf_laboratorium" && (!roomIds || roomIds.length === 0)) {
      return res.json({ success: true, data: [], message: "Data inventaris berhasil diambil" });
    }

    const assets = await InventoryModel.findAll({ search, status, condition, label_status, lab_id, roomIds, receipt_id, sort });

    res.json({ success: true, data: assets, message: "Data inventaris berhasil diambil" });
  } catch (error) {
    console.error("[INVENTORY ASSETS ERROR]", error);
    res.status(500).json({ success: false, message: "Gagal mengambil data inventaris", errors: { detail: error.message } });
  }
};

const getInventoryBatches = async (req, res) => {
  try {
    const { label_status, search, page = 1, limit = 10 } = req.query;

    let whereClause = "";
    let params = [];
    if (search) {
      whereClause = "WHERE pd.title LIKE ? OR l.name LIKE ?";
      params = [`%${search}%`, `%${search}%`];
    }

    const [rows] = await db.query(`
      SELECT
        gr.id                              AS receipt_id,
        gr.received_date,
        gr.quantity_received,
        pd.id                              AS draft_id,
        pd.title                           AS draft_title,
        pd.budget_year,
        l.name                             AS lab_name,
        l.code                             AS lab_code,
        pi.item_name,
        COALESCE(ic.name, pi.item_name)    AS catalog_name,
        COUNT(ia.id)                       AS total_assets,
        SUM(CASE WHEN ia.label_number IS NOT NULL AND ia.label_number != '' THEN 1 ELSE 0 END) AS labeled_count,
        SUM(CASE WHEN ia.label_number IS NULL OR ia.label_number = '' THEN 1 ELSE 0 END)       AS unlabeled_count
      FROM goods_receipts AS gr
      JOIN procurement_items AS pi  ON gr.procurement_item_id = pi.id
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      JOIN laboratories AS l        ON pd.lab_id = l.id
      LEFT JOIN item_catalogs AS ic ON pi.item_catalog_id = ic.id
      LEFT JOIN inventory_assets AS ia ON ia.receipt_id = gr.id
      ${whereClause}
      GROUP BY gr.id, pd.id, l.id, pi.id, ic.id
      HAVING total_assets > 0
      ORDER BY gr.received_date DESC, gr.id DESC
    `);

    // Group by receipt_id
    const batchMap = {};
    for (const row of rows) {
      const rid = row.receipt_id;
      if (!batchMap[rid]) {
        batchMap[rid] = {
          receipt_id:        rid,
          received_date:     row.received_date,
          draft_id:          row.draft_id,
          draft_title:       row.draft_title,
          budget_year:       row.budget_year,
          lab_name:          row.lab_name,
          lab_code:          row.lab_code,
          total_assets:      0,
          labeled_count:     0,
          unlabeled_count:   0,
          items:             []
        };
      }
      const b = batchMap[rid];
      b.total_assets    += Number(row.total_assets);
      b.labeled_count   += Number(row.labeled_count);
      b.unlabeled_count += Number(row.unlabeled_count);
      b.items.push({
        item_name:       row.item_name,
        catalog_name:    row.catalog_name,
        total_assets:    Number(row.total_assets),
        labeled_count:   Number(row.labeled_count),
        unlabeled_count: Number(row.unlabeled_count)
      });
    }

    let batches = Object.values(batchMap);

    if (label_status === 'unlabeled') {
      batches = batches.filter(b => b.unlabeled_count > 0);
    } else if (label_status === 'labeled') {
      batches = batches.filter(b => b.labeled_count > 0);
    }

    const total = batches.length;
    const startIndex = (page - 1) * limit;
    const endIndex = page * limit;
    const paginatedBatches = batches.slice(startIndex, endIndex);

    res.json({
      status: "success",
      data: paginatedBatches,
      meta: {
        total,
        page: Number(page),
        limit: Number(limit),
        last_page: Math.ceil(total / limit)
      }
    });
  } catch (error) {
    console.error("[INVENTORY BATCHES ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil data batch", detail: error.message });
  }
};

const getInventoryAsset = async (req, res) => {
  try {
    const { id } = req.params;
    const asset = await InventoryModel.findById(id);

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const canAccess = await checkAssetRoomAccess(req.user, asset, id);

    if (!canAccess) {
      return res.status(403).json({
        success: false,
        message: "Tidak boleh melihat aset dari ruangan/lab lain"
      });
    }

    let receiptHistory = [];

    if (asset.procurement_item_id) {
      receiptHistory = await InventoryModel.findTimelineReceipts(asset.procurement_item_id);
    }

    asset.receipt_history = receiptHistory;

    res.json({
      success: true,
      data: asset,
      message: "Detail aset berhasil diambil"
    });
  } catch (error) {
    console.error("[INVENTORY ASSET DETAIL ERROR]", error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil detail inventaris",
      errors: {
        detail: error.message
      }
    });
  }
};

const updateAssetLabel = async (req, res) => {
  const connection = await db.getConnection();

  try {
    await connection.beginTransaction();

    const { id } = req.params;
    const userId = req.user?.id;

    const label_number  = req.body.label_number;
    const asset_code    = req.body.asset_code    || null;
    const barcode       = req.body.barcode       || null;
    const serial_number = req.body.serial_number || null;
    const room_id       = req.body.room_id       || null;
    let photo_url       = req.body.photo_url     || null;

    if (!label_number) {
      await connection.rollback();
      return res.status(400).json({
        status:  "error",
        message: "Nomor label harus diisi",
        errors:  { label_number: "Wajib diisi" }
      });
    }

    const asset = await InventoryModel.findByIdForUpdate(id, connection);

    if (!asset) {
      await connection.rollback();
      return res.status(404).json({
        status:  "error",
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const dupLabel = await InventoryModel.findByLabelNumberExcludeId(label_number, id, connection);

    if (dupLabel) {
      await connection.rollback();
      return res.status(400).json({
        status:  "error",
        message: `Nomor label '${label_number}' sudah digunakan oleh aset lain`,
        errors:  { label_number: "Sudah digunakan" }
      });
    }

    const baseUrl = `${req.protocol}://${req.get("host")}`;

    let qr_url = null;

    if (req.files && req.files.qr_photo && req.files.qr_photo[0]) {
      qr_url = `${baseUrl}/uploads/qr/${req.files.qr_photo[0].filename}`;
    }
    if (req.files && req.files.asset_photo && req.files.asset_photo[0]) {
      photo_url = `${baseUrl}/uploads/assets/${req.files.asset_photo[0].filename}`;
    }

    // Auto-generate QR code dari label_number jika tidak ada upload manual
    if (!qr_url) {
    try {
      const qrDir = path.join(__dirname, "../../uploads/qr");
      if (!fs.existsSync(qrDir)) fs.mkdirSync(qrDir, { recursive: true });

      const qrFilename = `qr-label-${id}-${Date.now()}.png`;
      const qrFilepath = path.join(qrDir, qrFilename);

      await QRCode.toFile(qrFilepath, label_number, {
        width: 400,
        margin: 2,
        color: { dark: "#1e293b", light: "#ffffff" }
      });

      qr_url = `${baseUrl}/uploads/qr/${qrFilename}`;
    } catch (qrErr) {
      console.warn("[QR GENERATE WARN]", qrErr.message);
    }
    }

    await InventoryModel.updateLabel(id, {
      label_number,
      asset_code,
      barcode,
      photo_url,
      qr_url,
      serial_number,
      room_id
    }, connection);

    await InventoryModel.createConditionLog({
      inventory_asset_id: id,
      updated_by: userId,
      old_condition: asset.asset_condition,
      new_condition: asset.asset_condition,
      note: "Label assigned"
    }, connection);

    await connection.commit();

    res.json({
      status:  "success",
      message: "Label dan QR berhasil disimpan",
      data: {
        id,
        label_number,
        asset_code:    asset_code    || asset.asset_code,
        serial_number: serial_number || null,
        room_id:       room_id       ? Number(room_id) : null,
        barcode:       barcode       || null,
        photo_url:     photo_url     || null,
        qr_code:       qr_url        || null,
        status:        "labeled"
      }
    });
  } catch (error) {
    try { await connection.rollback(); } catch (_) {}
    console.error("[INVENTORY LABEL UPDATE ERROR]", error);
    res.status(500).json({
      status:  "error",
      message: "Gagal memperbarui label inventaris",
      detail:  error.message
    });
  } finally {
    connection.release();
  }
};

const getAssetTimeline = async (req, res) => {
  try {
    const { id } = req.params;
    const asset = await InventoryModel.findById(id);

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const canAccess = await checkAssetRoomAccess(req.user, asset, id);

    if (!canAccess) {
      return res.status(403).json({
        success: false,
        message: "Tidak boleh melihat timeline aset dari ruangan/lab lain"
      });
    }

    let timeline = [];

    if (asset.procurement_item_id) {
      const procurement = await InventoryModel.findTimelineProcurement(asset.procurement_item_id);

      if (procurement) {
        timeline.push({
          type: "procurement",
          title: "Pengadaan",
          description: `Diajukan dalam draf "${procurement.draft_title}" (${procurement.budget_year})`,
          detail: `${procurement.item_name} — estimasi Rp ${Number(procurement.estimated_price).toLocaleString("id")} × ${procurement.quantity}`,
          date: procurement.finalized_at,
          user: procurement.created_by_name,
          status: procurement.review_status
        });
      }
    }

    if (asset.procurement_item_id) {
      const receipts = await InventoryModel.findTimelineReceipts(asset.procurement_item_id);

      receipts.forEach((receipt) => {
        timeline.push({
          type: "receipt",
          title: "Penerimaan Barang",
          description: `Diterima ${receipt.quantity_received} unit`,
          detail: receipt.note || null,
          date: receipt.received_date,
          user: receipt.received_by_name,
          status: "received"
        });
      });
    }

    const conditionLogs = await InventoryModel.findTimelineConditionLogs(id);

    conditionLogs.forEach((log) => {
      timeline.push({
        type: "condition_change",
        title: "Perubahan Kondisi / Label",
        description: `${log.old_condition || "—"} → ${log.new_condition}`,
        detail: log.note,
        date: log.updated_at,
        user: log.updated_by_name,
        status: log.new_condition
      });
    });

    const maintenanceLogs = await InventoryModel.findTimelineMaintenance(id);

    maintenanceLogs.forEach((log) => {
      timeline.push({
        type: "maintenance",
        title: "Maintenance",
        description: log.issue_description || "Pemeliharaan rutin",
        detail: log.action_taken,
        date: log.maintenance_date,
        user: log.performed_by_name,
        status: log.status,
        cost: log.cost
      });
    });

    const disposalRows = await InventoryModel.findTimelineDisposal(id);

    disposalRows.forEach((row) => {
      timeline.push({
        type: "disposal",
        title: "Penghapusan Aset",
        description: row.reason,
        detail: row.disposal_note,
        date: row.disposal_date,
        user: row.disposed_by_name,
        status: "disposed"
      });
    });

    timeline.sort((a, b) => {
      const dateA = a.date ? new Date(a.date) : new Date(0);
      const dateB = b.date ? new Date(b.date) : new Date(0);
      return dateA - dateB;
    });

    res.json({
      success: true,
      data: timeline,
      message: "Timeline aset berhasil diambil"
    });
  } catch (error) {
    console.error("[ASSET TIMELINE ERROR]", error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil timeline aset",
      errors: {
        detail: error.message
      }
    });
  }
};

const getConditionHistory = async (req, res) => {
  try {
    const roomIds = await getAccessibleRoomIdsForUser(req.user);

    if (req.user?.role === "staf_laboratorium" && (!roomIds || roomIds.length === 0)) {
      return res.json({
        success: true,
        data: [],
        message: "History kondisi aset berhasil diambil"
      });
    }

    const history = await InventoryModel.findConditionHistory({
      search: req.query.search,
      condition: req.query.condition,
      roomIds
    });

    res.json({
      success: true,
      data: history,
      message: "History kondisi aset berhasil diambil"
    });
  } catch (error) {
    console.error("[CONDITION HISTORY ERROR]", error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil history kondisi aset",
      errors: {
        detail: error.message
      }
    });
  }
};

const updateAssetCondition = async (req, res) => {
  const connection = await db.getConnection();

  try {
    await connection.beginTransaction();

    const { id } = req.params;
    const { asset_condition, note } = req.body;

    const validConditions = [
      "baik",
      "rusak_ringan",
      "rusak_berat",
      "maintenance",
      "dihapus",
      "diganti"
    ];

    if (!validConditions.includes(asset_condition)) {
      await connection.rollback();

      return res.status(400).json({
        success: false,
        message: "Kondisi aset tidak valid"
      });
    }

    const asset = await InventoryModel.findByIdForUpdate(id, connection);

    if (!asset) {
      await connection.rollback();

      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const canAccess = await checkAssetRoomAccess(req.user, asset, id);

    if (!canAccess) {
      await connection.rollback();

      return res.status(403).json({
        success: false,
        message: "Tidak boleh update aset dari ruangan/lab lain"
      });
    }

    const nextStatus =
      asset_condition === "maintenance"
        ? "maintenance"
        : asset_condition === "dihapus"
          ? "disposed"
          : asset_condition === "diganti"
            ? "replaced"
            : "available";

    await InventoryModel.updateCondition(id, {
      condition: asset_condition,
      status: nextStatus,
      note
    }, connection);

    await InventoryModel.createConditionLog({
      inventory_asset_id: id,
      updated_by: req.user?.id,
      old_condition: asset.asset_condition,
      new_condition: asset_condition,
      note: note || "Update kondisi aset"
    }, connection);

    await connection.commit();

    res.json({
      success: true,
      message: "Kondisi aset berhasil diperbarui"
    });
  } catch (error) {
    try {
      await connection.rollback();
    } catch (_) {}

    console.error("[UPDATE CONDITION ERROR]", error);

    res.status(500).json({
      success: false,
      message: "Gagal memperbarui kondisi aset",
      errors: {
        detail: error.message
      }
    });
  } finally {
    connection.release();
  }
};

const checkLabelAvailability = async (req, res) => {
  try {
    const { label, exclude_id } = req.query;

    if (!label || !label.trim()) {
      return res.json({
        status: "success",
        data: { available: false, message: "Label kosong" }
      });
    }

    const dup = await InventoryModel.findByLabelNumberExcludeId(
      label.trim(),
      exclude_id || 0
    );

    res.json({
      status: "success",
      data: {
        available: !dup,
        message: dup
          ? `Label '${label}' sudah dipakai oleh aset lain`
          : "Label tersedia"
      }
    });
  } catch (error) {
    console.error("[LABEL CHECK ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengecek ketersediaan label"
    });
  }
};

module.exports = {
  getInventoryAssets,
  getInventoryBatches,
  getInventoryAsset,
  updateAssetLabel,
  checkLabelAvailability,
  getAssetTimeline,
  getConditionHistory,
  updateAssetCondition
};