const InventoryModel = require("../models/InventoryModel");
const db = require("../config/database"); // needed for transactions connection retrieval
const path = require("path");
const fs = require("fs");

const getInventoryAssets = async (req, res, next) => {
  try {
    const { search, status, condition, label_status, lab_id } = req.query;
    const assets = await InventoryModel.findAll({ search, status, condition, label_status, lab_id });

    res.json({
      success: true,
      data:    assets,
      message: "Data inventaris berhasil diambil"
    });
  } catch (error) {
    console.error("[INVENTORY ASSETS ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil data inventaris",
      errors:  { detail: error.message }
    });
  }
};

const getInventoryAsset = async (req, res, next) => {
  try {
    const { id } = req.params;
    const asset = await InventoryModel.findById(id);

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    let receiptHistory = [];
    if (asset.procurement_item_id) {
      receiptHistory = await InventoryModel.findTimelineReceipts(asset.procurement_item_id);
    }

    asset.receipt_history = receiptHistory;

    res.json({
      success: true,
      data:    asset,
      message: "Detail aset berhasil diambil"
    });
  } catch (error) {
    console.error("[INVENTORY ASSET DETAIL ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil detail inventaris",
      errors:  { detail: error.message }
    });
  }
};

const updateAssetLabel = async (req, res, next) => {
  const connection = await db.getConnection();
  try {
    await connection.beginTransaction();

    const { id } = req.params;
    const userId = req.user?.id;

    const label_number = req.body.label_number;
    const asset_code   = req.body.asset_code   || null;
    const barcode      = req.body.barcode       || null;
    let   photo_url    = req.body.photo_url     || null;

    if (!label_number) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: "Nomor label harus diisi",
        errors:  { label_number: "Wajib diisi" }
      });
    }

    const asset = await InventoryModel.findByIdForUpdate(id, connection);

    if (!asset) {
      await connection.rollback();
      connection.release();
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    const dupLabel = await InventoryModel.findByLabelNumberExcludeId(label_number, id, connection);
    if (dupLabel) {
      await connection.rollback();
      connection.release();
      return res.status(400).json({
        success: false,
        message: `Nomor label '${label_number}' sudah digunakan oleh aset lain`,
        errors:  { label_number: "Sudah digunakan" }
      });
    }

    if (req.file) {
      const uploadsDir = path.join(__dirname, '../../uploads/assets');
      if (!fs.existsSync(uploadsDir)) {
        fs.mkdirSync(uploadsDir, { recursive: true });
      }
      photo_url = `/uploads/assets/${req.file.filename}`;
    }

    await InventoryModel.updateLabel(id, {
      label_number,
      asset_code,
      barcode,
      photo_url
    }, connection);

    await InventoryModel.createConditionLog({
      inventory_asset_id: id,
      updated_by: userId,
      old_condition: asset.asset_condition,
      new_condition: asset.asset_condition,
      note: 'Label assigned'
    }, connection);

    await connection.commit();
    connection.release();

    res.json({
      success: true,
      message: "Label dan foto berhasil diperbarui. Status diubah ke 'labeled'.",
      data: {
        id,
        label_number,
        asset_code:  asset_code  || asset.asset_code,
        barcode:     barcode     || null,
        photo_url:   photo_url   || null,
        status:      'labeled'
      }
    });
  } catch (error) {
    try { await connection.rollback(); } catch (_) {}
    connection.release();
    console.error("[INVENTORY LABEL UPDATE ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal memperbarui label inventaris",
      errors:  { detail: error.message }
    });
  }
};

const getAssetTimeline = async (req, res, next) => {
  try {
    const { id } = req.params;
    const asset = await InventoryModel.findById(id);

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: "Aset inventaris tidak ditemukan"
      });
    }

    let timeline = [];

    // 1. Procurement origin
    if (asset.procurement_item_id) {
      const p = await InventoryModel.findTimelineProcurement(asset.procurement_item_id);
      if (p) {
        timeline.push({
          type:        'procurement',
          title:       'Pengadaan',
          description: `Diajukan dalam draf "${p.draft_title}" (${p.budget_year})`,
          detail:      `${p.item_name} — estimasi Rp ${Number(p.estimated_price).toLocaleString('id')} × ${p.quantity}`,
          date:        p.finalized_at,
          user:        p.created_by_name,
          status:      p.review_status
        });
      }
    }

    // 2. All goods_receipt records for this procurement_item
    if (asset.procurement_item_id) {
      const receipts = await InventoryModel.findTimelineReceipts(asset.procurement_item_id);
      receipts.forEach(r => {
        timeline.push({
          type:        'receipt',
          title:       'Penerimaan Barang',
          description: `Diterima ${r.quantity_received} unit`,
          detail:      r.note || null,
          date:        r.received_date,
          user:        r.received_by_name,
          status:      'received'
        });
      });
    }

    // 3. Condition logs
    const condLogs = await InventoryModel.findTimelineConditionLogs(id);
    condLogs.forEach(log => {
      timeline.push({
        type:        'condition_change',
        title:       'Perubahan Kondisi / Label',
        description: `${log.old_condition || '—'} → ${log.new_condition}`,
        detail:      log.note,
        date:        log.updated_at,
        user:        log.updated_by_name,
        status:      log.new_condition
      });
    });

    // 4. Maintenance logs
    const maintLogs = await InventoryModel.findTimelineMaintenance(id);
    maintLogs.forEach(log => {
      timeline.push({
        type:        'maintenance',
        title:       'Maintenance',
        description: log.issue_description || 'Pemeliharaan rutin',
        detail:      log.action_taken,
        date:        log.maintenance_date,
        user:        log.performed_by_name,
        status:      log.status,
        cost:        log.cost
      });
    });

    // 5. Disposal
    const disposalRows = await InventoryModel.findTimelineDisposal(id);
    disposalRows.forEach(row => {
      timeline.push({
        type:        'disposal',
        title:       'Penghapusan Aset',
        description: row.reason,
        detail:      row.disposal_note,
        date:        row.disposal_date,
        user:        row.disposed_by_name,
        status:      'disposed'
      });
    });

    // Sort chronologically
    timeline.sort((a, b) => {
      const da = a.date ? new Date(a.date) : new Date(0);
      const db_ = b.date ? new Date(b.date) : new Date(0);
      return da - db_;
    });

    res.json({
      success: true,
      data:    timeline,
      message: "Timeline aset berhasil diambil"
    });
  } catch (error) {
    console.error("[ASSET TIMELINE ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil timeline aset",
      errors:  { detail: error.message }
    });
  }
};

module.exports = {
  getInventoryAssets,
  getInventoryAsset,
  updateAssetLabel,
  getAssetTimeline
};
