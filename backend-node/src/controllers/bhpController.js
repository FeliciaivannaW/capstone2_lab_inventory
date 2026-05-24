const db = require("../config/database");

const getCurrentUser = async (userId) => {
  const [rows] = await db.query(`
    SELECT u.id, u.lab_id, r.name AS role
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.id = ?
    LIMIT 1
  `, [userId]);
  return rows[0] || null;
};

const parsePositiveInt = (value, fieldName) => {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) {
    const error = new Error(`${fieldName} harus berupa angka lebih dari 0`);
    error.statusCode = 400;
    throw error;
  }
  return parsed;
};

const getBhpCatalogs = async (req, res) => {
  try {
    const [catalogs] = await db.query(`
      SELECT id, name, unit, description
      FROM item_catalogs
      WHERE type = 'bhp'
      ORDER BY name ASC
    `);
    res.json({ status: "success", data: catalogs });
  } catch (error) {
    console.error("[GET BHP CATALOGS ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil katalog BHP" });
  }
};

const getStocks = async (req, res) => {
  try {
    const currentUser = await getCurrentUser(req.user.id);
    const { lab_id, search, low_stock } = req.query;
    const conditions = ["ic.type = 'bhp'"];
    const params = [];

    if (currentUser.role === "staf_laboratorium") {
      if (!currentUser.lab_id) {
        return res.status(400).json({ status: "error", message: "User staf laboratorium belum terhubung ke laboratorium" });
      }
      conditions.push("bs.lab_id = ?");
      params.push(currentUser.lab_id);
    } else if (lab_id) {
      conditions.push("bs.lab_id = ?");
      params.push(Number(lab_id));
    }

    if (search) {
      conditions.push("(ic.name LIKE ? OR l.name LIKE ?)");
      params.push(`%${search}%`, `%${search}%`);
    }

    if (low_stock === "1" || low_stock === "true") {
      conditions.push("bs.current_stock <= bs.minimum_stock");
    }

    const [stocks] = await db.query(`
      SELECT
        bs.id,
        bs.lab_id,
        l.name AS laboratory_name,
        bs.item_catalog_id,
        ic.name AS item_name,
        ic.unit AS catalog_unit,
        bs.unit,
        bs.current_stock,
        bs.minimum_stock,
        CASE
          WHEN bs.current_stock <= bs.minimum_stock THEN 'kritis'
          WHEN bs.current_stock <= (bs.minimum_stock * 2) THEN 'menipis'
          ELSE 'aman'
        END AS stock_status,
        bs.updated_at
      FROM bhp_stocks bs
      JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
      JOIN laboratories l ON bs.lab_id = l.id
      WHERE ${conditions.join(" AND ")}
      ORDER BY l.name ASC, ic.name ASC
    `, params);

    res.json({ status: "success", data: stocks });
  } catch (error) {
    console.error("[GET BHP STOCKS ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal mengambil stok BHP" });
  }
};

const getStockMovements = async (req, res) => {
  try {
    const stockId = parsePositiveInt(req.params.id, "ID stok");
    const currentUser = await getCurrentUser(req.user.id);

    const [stocks] = await db.query("SELECT id, lab_id FROM bhp_stocks WHERE id = ? LIMIT 1", [stockId]);
    if (!stocks.length) {
      return res.status(404).json({ status: "error", message: "Stok BHP tidak ditemukan" });
    }

    if (currentUser.role === "staf_laboratorium" && stocks[0].lab_id !== currentUser.lab_id) {
      return res.status(403).json({ status: "error", message: "Tidak boleh melihat stok lab lain" });
    }

    const [movements] = await db.query(`
      SELECT
        m.id,
        m.stock_id,
        m.movement_type,
        m.quantity,
        m.movement_date,
        m.note,
        m.procurement_item_id,
        m.receipt_id,
        m.maintenance_id,
        u.name AS performed_by_name
      FROM bhp_stock_movements m
      JOIN users u ON m.performed_by = u.id
      WHERE m.stock_id = ?
      ORDER BY m.movement_date DESC, m.id DESC
    `, [stockId]);

    res.json({ status: "success", data: movements });
  } catch (error) {
    console.error("[GET BHP MOVEMENTS ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal mengambil riwayat stok" });
  }
};

const createStock = async (req, res) => {
  const connection = await db.getConnection();
  try {
    const currentUser = await getCurrentUser(req.user.id);
    let labId = req.body.lab_id ? Number(req.body.lab_id) : currentUser.lab_id;

    if (currentUser.role === "staf_laboratorium") {
      labId = currentUser.lab_id;
    }

    if (!labId) {
      return res.status(400).json({ status: "error", message: "Laboratorium wajib dipilih" });
    }

    let itemCatalogId = req.body.item_catalog_id ? Number(req.body.item_catalog_id) : null;
    const { item_name, unit = "pcs", minimum_stock = 0, initial_stock = 0 } = req.body;

    await connection.beginTransaction();

    if (!itemCatalogId) {
      if (!item_name) {
        throw Object.assign(new Error("Pilih katalog BHP atau isi nama item baru"), { statusCode: 400 });
      }
      const [catalogResult] = await connection.query(`
        INSERT INTO item_catalogs (category_id, name, type, unit, description)
        VALUES (NULL, ?, 'bhp', ?, NULL)
      `, [item_name, unit]);
      itemCatalogId = catalogResult.insertId;
    }

    const [catalogs] = await connection.query("SELECT id, unit FROM item_catalogs WHERE id = ? AND type = 'bhp' LIMIT 1", [itemCatalogId]);
    if (!catalogs.length) {
      throw Object.assign(new Error("Katalog BHP tidak valid"), { statusCode: 400 });
    }

    const qty = Number(initial_stock) || 0;
    const minStock = Number(minimum_stock) || 0;
    if (qty < 0 || minStock < 0) {
      throw Object.assign(new Error("Stok awal dan minimum stok tidak boleh negatif"), { statusCode: 400 });
    }

    const [existing] = await connection.query(
      "SELECT id FROM bhp_stocks WHERE lab_id = ? AND item_catalog_id = ? LIMIT 1",
      [labId, itemCatalogId]
    );
    if (existing.length) {
      throw Object.assign(new Error("BHP ini sudah terdaftar pada lab tersebut"), { statusCode: 409 });
    }

    const stockUnit = unit || catalogs[0].unit || "pcs";
    const [stockResult] = await connection.query(`
      INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit)
      VALUES (?, ?, ?, ?, ?)
    `, [labId, itemCatalogId, qty, minStock, stockUnit]);

    if (qty > 0) {
      await connection.query(`
        INSERT INTO bhp_stock_movements (stock_id, performed_by, movement_type, quantity, note)
        VALUES (?, ?, 'in', ?, 'Stok awal')
      `, [stockResult.insertId, req.user.id, qty]);
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
    const { minimum_stock, unit } = req.body;
    const currentUser = await getCurrentUser(req.user.id);

    const [stocks] = await db.query("SELECT id, lab_id FROM bhp_stocks WHERE id = ? LIMIT 1", [stockId]);
    if (!stocks.length) {
      return res.status(404).json({ status: "error", message: "Stok BHP tidak ditemukan" });
    }

    if (currentUser.role === "staf_laboratorium" && stocks[0].lab_id !== currentUser.lab_id) {
      return res.status(403).json({ status: "error", message: "Tidak boleh mengubah stok lab lain" });
    }

    await db.query(`
      UPDATE bhp_stocks
      SET minimum_stock = ?, unit = ?
      WHERE id = ?
    `, [Number(minimum_stock) || 0, unit || "pcs", stockId]);

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
    const quantity = parsePositiveInt(req.body.quantity, "Jumlah");
    const movementType = req.body.movement_type;
    const note = req.body.note || null;

    if (!["in", "out", "adjustment"].includes(movementType)) {
      return res.status(400).json({ status: "error", message: "Tipe pergerakan stok tidak valid" });
    }

    const currentUser = await getCurrentUser(req.user.id);
    await connection.beginTransaction();

    const [stocks] = await connection.query("SELECT id, lab_id, current_stock FROM bhp_stocks WHERE id = ? FOR UPDATE", [stockId]);
    if (!stocks.length) {
      throw Object.assign(new Error("Stok BHP tidak ditemukan"), { statusCode: 404 });
    }

    if (currentUser.role === "staf_laboratorium" && stocks[0].lab_id !== currentUser.lab_id) {
      throw Object.assign(new Error("Tidak boleh mengubah stok lab lain"), { statusCode: 403 });
    }

    let newStock = Number(stocks[0].current_stock);
    if (movementType === "in") newStock += quantity;
    if (movementType === "out") newStock -= quantity;
    if (movementType === "adjustment") newStock = quantity;

    if (newStock < 0) {
      throw Object.assign(new Error("Stok tidak cukup untuk dikurangi"), { statusCode: 400 });
    }

    await connection.query("UPDATE bhp_stocks SET current_stock = ? WHERE id = ?", [newStock, stockId]);
    await connection.query(`
      INSERT INTO bhp_stock_movements (stock_id, performed_by, movement_type, quantity, note)
      VALUES (?, ?, ?, ?, ?)
    `, [stockId, req.user.id, movementType, quantity, note]);

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