const db = require("../config/database");
const MaintenanceModel = require("../models/MaintenanceModel");
const InventoryModel = require("../models/InventoryModel");
const LabAccessModel = require("../models/LabAccessModel");

const getAccessibleLabIdsOrFail = async (userId) => {
  const currentUser = await LabAccessModel.findCurrentUser(userId);

  if (!currentUser) {
    throw Object.assign(new Error("User tidak ditemukan"), { statusCode: 404 });
  }

  if (currentUser.role !== "staf_laboratorium") {
    throw Object.assign(new Error("Maintenance hanya untuk Staf Laboratorium"), { statusCode: 403 });
  }

  const labIds = await LabAccessModel.findAccessibleLabIds(userId);

  if (!labIds.length) {
    throw Object.assign(new Error("User staf laboratorium belum memiliki akses ke laboratorium/grup lab"), { statusCode: 400 });
  }

  return labIds.map((id) => Number(id));
};

const normalizeBhpUsages = (value) => {
  if (!value) return [];

  if (typeof value === "string") {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch (_) {
      return [];
    }
  }

  return Array.isArray(value) ? value : [];
};

const hasBhpUsage = (bhpUsages) => {
  return bhpUsages.some((usage) => {
    if (!usage) return false;

    const stockId = Number(usage.stock_id);
    const qty = Number(usage.quantity);

    return Number.isInteger(stockId) && stockId > 0 && Number.isInteger(qty) && qty > 0;
  });
};

const getMaintenanceLogs = async (req, res) => {
  try {
    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);

    const logs = await MaintenanceModel.findLogs({
      assetId: req.query.asset_id,
      status: req.query.status,
      search: req.query.search,
      labIds: accessibleLabIds
    });

    if (!logs.length) {
      return res.json({
        status: "success",
        data: []
      });
    }

    const usageRows = await MaintenanceModel.findBhpUsages(
      logs.map((log) => log.id)
    );

    const usageMap = usageRows.reduce((acc, row) => {
      if (!acc[row.maintenance_id]) {
        acc[row.maintenance_id] = [];
      }

      acc[row.maintenance_id].push(row);
      return acc;
    }, {});

    const result = logs.map((log) => ({
      ...log,
      bhp_usages: usageMap[log.id] || []
    }));

    res.json({
      status: "success",
      data: result
    });
  } catch (error) {
    console.error("[GET MAINTENANCE ERROR]", error);

    res.status(error.statusCode || 500).json({
      status: "error",
      message: error.message || "Gagal mengambil log maintenance"
    });
  }
};

const createMaintenanceLog = async (req, res) => {
  const connection = await db.getConnection();

  try {
    const {
      inventory_asset_id,
      maintenance_date,
      issue_description,
      action_taken,
      condition_after,
      status = "done",
      cost = 0,
      notes = null
    } = req.body;

    const bhpUsages = normalizeBhpUsages(req.body.bhp_usages);

    if (!inventory_asset_id || !maintenance_date || !condition_after) {
      return res.status(400).json({
        status: "error",
        message: "Aset, tanggal maintenance, dan kondisi akhir wajib diisi"
      });
    }

    const validStatuses = ["planned", "in_progress", "done", "cancelled"];

    if (!validStatuses.includes(status)) {
      return res.status(400).json({
        status: "error",
        message: "Status maintenance tidak valid"
      });
    }

    const validConditions = [
      "baik",
      "rusak_ringan",
      "rusak_berat",
      "maintenance",
      "dihapus",
      "diganti"
    ];

    if (!validConditions.includes(condition_after)) {
      return res.status(400).json({
        status: "error",
        message: "Kondisi akhir tidak valid"
      });
    }

    if (status !== "done" && hasBhpUsage(bhpUsages)) {
      return res.status(400).json({
        status: "error",
        message: "Pemakaian BHP hanya boleh dicatat ketika maintenance berstatus done"
      });
    }

    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);

    await connection.beginTransaction();

    const asset = await MaintenanceModel.findAssetForMaintenance(
      Number(inventory_asset_id),
      connection
    );

    if (!asset) {
      throw Object.assign(new Error("Aset inventaris tidak ditemukan"), {
        statusCode: 404
      });
    }

    if (asset.lab_id && !accessibleLabIds.includes(Number(asset.lab_id))) {
      throw Object.assign(new Error("Tidak boleh maintenance aset dari lab lain"), {
        statusCode: 403
      });
    }

    const result = await MaintenanceModel.createLog({
      inventoryAssetId: Number(inventory_asset_id),
      performedBy: req.user.id,
      maintenanceDate: maintenance_date,
      issueDescription: issue_description || null,
      actionTaken: action_taken || null,
      conditionBefore: asset.asset_condition,
      conditionAfter: condition_after,
      status,
      cost: Number(cost) || 0,
      notes
    }, connection);

    const maintenanceId = result.insertId;

    if (status !== "cancelled" && condition_after !== asset.asset_condition) {
      const nextStatus =
        condition_after === "maintenance" || status === "in_progress"
          ? "maintenance"
          : condition_after === "dihapus"
            ? "disposed"
            : condition_after === "diganti"
              ? "replaced"
              : asset.status === "received"
                ? "received"
                : "available";

      await MaintenanceModel.updateAssetStatus(
        Number(inventory_asset_id),
        condition_after,
        nextStatus,
        connection
      );

      await InventoryModel.createConditionLog({
        inventory_asset_id: Number(inventory_asset_id),
        updated_by: req.user.id,
        old_condition: asset.asset_condition,
        new_condition: condition_after,
        note: `Update dari maintenance #${maintenanceId}`
      }, connection);
    }

    if (status === "done") {
      for (const usage of bhpUsages) {
        if (!usage || !usage.stock_id || !usage.quantity) continue;

        const stockId = Number(usage.stock_id);
        const qty = Number(usage.quantity);

        if (
          !Number.isInteger(stockId) ||
          stockId <= 0 ||
          !Number.isInteger(qty) ||
          qty <= 0
        ) {
          throw Object.assign(new Error("Data pemakaian BHP tidak valid"), {
            statusCode: 400
          });
        }

        const stock = await MaintenanceModel.findBhpStockForUpdate(
          stockId,
          connection
        );

        if (!stock) {
          throw Object.assign(new Error("Stok BHP tidak ditemukan"), {
            statusCode: 404
          });
        }

        if (!accessibleLabIds.includes(Number(stock.lab_id))) {
          throw Object.assign(new Error("Tidak boleh memakai stok BHP dari lab lain"), {
            statusCode: 403
          });
        }

        if (Number(stock.current_stock) < qty) {
          throw Object.assign(new Error("Stok BHP tidak cukup untuk maintenance"), {
            statusCode: 400
          });
        }

        await MaintenanceModel.updateBhpStockQty(stockId, qty, connection);

        await MaintenanceModel.createBhpStockMovement({
          stockId,
          maintenanceId,
          performedBy: req.user.id,
          quantity: qty,
          note: usage.note || `Pemakaian untuk maintenance #${maintenanceId}`
        }, connection);
      }
    }

    await connection.commit();

    res.status(201).json({
      status: "success",
      message: "Log maintenance berhasil disimpan",
      data: {
        id: maintenanceId
      }
    });
  } catch (error) {
    try {
      await connection.rollback();
    } catch (_) {}

    console.error("[CREATE MAINTENANCE ERROR]", error);

    res.status(error.statusCode || 500).json({
      status: "error",
      message: error.message || "Gagal menyimpan log maintenance"
    });
  } finally {
    connection.release();
  }
};

module.exports = {
  getMaintenanceLogs,
  createMaintenanceLog
};