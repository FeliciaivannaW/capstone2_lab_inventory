const db = require("../config/database");
const BhpModel = require("../models/BhpModel");
const LabAccessModel = require("../models/LabAccessModel");

const parsePositiveInt = (value, fieldName) => {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) {
    const error = new Error(`${fieldName} harus berupa angka lebih dari 0`);
    error.statusCode = 400;
    throw error;
  }
  return parsed;
};

const getAccessibleLabIdsOrFail = async (userId) => {
  const currentUser = await LabAccessModel.findCurrentUser(userId);
  if (!currentUser) {
    throw Object.assign(new Error("User tidak ditemukan"), { statusCode: 404 });
  }

  if (currentUser.role !== "staf_laboratorium") {
    throw Object.assign(new Error("Kelola BHP hanya untuk Staf Laboratorium"), { statusCode: 403 });
  }

  const labIds = await LabAccessModel.findAccessibleLabIds(userId);
  if (!labIds.length) {
    throw Object.assign(new Error("User staf laboratorium belum memiliki akses ke laboratorium/grup lab"), { statusCode: 400 });
  }

  return labIds;
};

const getBhpCatalogs = async (req, res) => {
  try {
    const catalogs = await BhpModel.findCatalogs();
    res.json({ status: "success", data: catalogs });
  } catch (error) {
    console.error("[GET BHP CATALOGS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil katalog BHP" });
  }
};

const getStocks = async (req, res) => {
  try {
    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);
    const requestedLabId = req.query.lab_id ? Number(req.query.lab_id) : null;

    if (requestedLabId && !accessibleLabIds.includes(requestedLabId)) {
      return res.status(403).json({ status: "error", message: "Tidak boleh melihat stok lab lain" });
    }

    const stocks = await BhpModel.findStocks({
      labIds: requestedLabId ? [requestedLabId] : accessibleLabIds,
      search: req.query.search,
      lowStock: req.query.low_stock
    });

    res.json({ status: "success", data: stocks });
  } catch (error) {
    console.error("[GET BHP STOCKS ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal mengambil stok BHP" });
  }
};

const getStockMovements = async (req, res) => {
  try {
    const stockId = parsePositiveInt(req.params.id, "ID stok");
    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);

    const stock = await BhpModel.findStockById(stockId);
    if (!stock) {
      return res.status(404).json({ status: "error", message: "Stok BHP tidak ditemukan" });
    }

    if (!accessibleLabIds.includes(Number(stock.lab_id))) {
      return res.status(403).json({ status: "error", message: "Tidak boleh melihat stok lab lain" });
    }

    const movements = await BhpModel.findMovementsByStockId(stockId);
    res.json({ status: "success", data: movements });
  } catch (error) {
    console.error("[GET BHP MOVEMENTS ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal mengambil riwayat stok" });
  }
};

const createStock = async (req, res) => {
  const connection = await db.getConnection();
  try {
    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);
    let labId = req.body.lab_id ? Number(req.body.lab_id) : accessibleLabIds[0];

    if (!accessibleLabIds.includes(labId)) {
      return res.status(403).json({ status: "error", message: "Tidak boleh menambahkan stok untuk lab lain" });
    }

    let itemCatalogId = req.body.item_catalog_id ? Number(req.body.item_catalog_id) : null;
    const itemName = req.body.item_name ? String(req.body.item_name).trim() : "";
    const unit = req.body.unit ? String(req.body.unit).trim() : "pcs";
    const initialStock = Number(req.body.initial_stock) || 0;
    const minimumStock = Number(req.body.minimum_stock) || 0;

    if (initialStock < 0 || minimumStock < 0) {
      return res.status(400).json({ status: "error", message: "Stok awal dan minimum stok tidak boleh negatif" });
    }

    await connection.beginTransaction();

    if (!itemCatalogId) {
      if (!itemName) {
        throw Object.assign(new Error("Pilih katalog BHP atau isi nama item baru"), { statusCode: 400 });
      }
      const catalogResult = await BhpModel.createCatalog({ name: itemName, unit }, connection);
      itemCatalogId = catalogResult.insertId;
    }

    const catalog = await BhpModel.findCatalogById(itemCatalogId, connection);
    if (!catalog) {
      throw Object.assign(new Error("Katalog BHP tidak valid"), { statusCode: 400 });
    }

    const existing = await BhpModel.findStockByLabAndCatalog(labId, itemCatalogId, connection);
    if (existing) {
      throw Object.assign(new Error("BHP ini sudah terdaftar pada lab tersebut"), { statusCode: 409 });
    }

    const stockResult = await BhpModel.createStock({
      labId,
      itemCatalogId,
      currentStock: initialStock,
      minimumStock,
      unit: unit || catalog.unit || "pcs"
    }, connection);

    if (initialStock > 0) {
      await BhpModel.createMovement({
        stockId: stockResult.insertId,
        performedBy: req.user.id,
        movementType: "in",
        quantity: initialStock,
        note: "Stok awal"
      }, connection);
    }

    await connection.commit();
    res.status(201).json({ status: "success", message: "Stok BHP berhasil ditambahkan", data: { id: stockResult.insertId } });
  } catch (error) {
    await connection.rollback();
    console.error("[CREATE BHP STOCK ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menambahkan stok BHP" });
  } finally {
    connection.release();
  }
};

const updateStock = async (req, res) => {
  try {
    const stockId = parsePositiveInt(req.params.id, "ID stok");
    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);

    const stock = await BhpModel.findStockById(stockId);
    if (!stock) {
      return res.status(404).json({ status: "error", message: "Stok BHP tidak ditemukan" });
    }

    if (!accessibleLabIds.includes(Number(stock.lab_id))) {
      return res.status(403).json({ status: "error", message: "Tidak boleh mengubah stok lab lain" });
    }

    await BhpModel.updateStock(stockId, {
      minimumStock: Number(req.body.minimum_stock) || 0,
      unit: req.body.unit || "pcs"
    });

    res.json({ status: "success", message: "Data stok BHP berhasil diperbarui" });
  } catch (error) {
    console.error("[UPDATE BHP STOCK ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal memperbarui stok BHP" });
  }
};

const adjustStock = async (req, res) => {
  const connection = await db.getConnection();
  try {
    const stockId = parsePositiveInt(req.params.id, "ID stok");
    const movementType = req.body.movement_type;
    const isAdjustment = movementType === "adjustment";
    const parsedQty = Number(req.body.quantity);

    if (!Number.isInteger(parsedQty) || parsedQty < 0 || (!isAdjustment && parsedQty === 0)) {
      return res.status(400).json({ status: "error", message: isAdjustment ? "Jumlah tidak boleh negatif" : "Jumlah harus lebih dari 0" });
    }
    const quantity = parsedQty;
    
    const note = req.body.note || null;

    if (!["in", "out", "adjustment"].includes(movementType)) {
      return res.status(400).json({ status: "error", message: "Tipe pergerakan stok tidak valid" });
    }

    const accessibleLabIds = await getAccessibleLabIdsOrFail(req.user.id);

    await connection.beginTransaction();

    const stock = await BhpModel.findStockByIdForUpdate(stockId, connection);
    if (!stock) {
      throw Object.assign(new Error("Stok BHP tidak ditemukan"), { statusCode: 404 });
    }

    if (!accessibleLabIds.includes(Number(stock.lab_id))) {
      throw Object.assign(new Error("Tidak boleh mengubah stok lab lain"), { statusCode: 403 });
    }

    let newStock = Number(stock.current_stock);
    if (movementType === "in") newStock += quantity;
    if (movementType === "out") newStock -= quantity;
    if (movementType === "adjustment") newStock = quantity;

    if (newStock < 0) {
      throw Object.assign(new Error("Stok tidak cukup untuk dikurangi"), { statusCode: 400 });
    }

    await BhpModel.updateStockQty(stockId, newStock, connection);
    await BhpModel.createMovement({
      stockId,
      performedBy: req.user.id,
      movementType,
      quantity,
      note
    }, connection);

    await connection.commit();
    res.json({ status: "success", message: "Stok BHP berhasil diperbarui", data: { current_stock: newStock } });
  } catch (error) {
    await connection.rollback();
    console.error("[ADJUST BHP STOCK ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal mengubah stok BHP" });
  } finally {
    connection.release();
  }
};

module.exports = {
  getBhpCatalogs,
  getStocks,
  getStockMovements,
  createStock,
  updateStock,
  adjustStock
};