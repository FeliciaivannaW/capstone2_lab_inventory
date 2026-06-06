const db = require("../config/database");

const BhpModel = {
  async findCatalogs() {
    const [catalogs] = await db.query(`
      SELECT id, name, unit, description
      FROM item_catalogs
      WHERE type = 'bhp'
      ORDER BY name ASC
    `);
    return catalogs;
  },

  async findStocks({ labIds = [], labId, search, lowStock }) {
    const conditions = ["ic.type = 'bhp'"];
    const params = [];

    if (labIds.length) {
      conditions.push("bs.lab_id IN (?)");
      params.push(labIds);
    } else if (labId) {
      conditions.push("bs.lab_id = ?");
      params.push(Number(labId));
    }

    if (search) {
      conditions.push("(ic.name LIKE ? OR l.name LIKE ? OR l.code LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }

    if (lowStock === "1" || lowStock === "true") {
      conditions.push("bs.current_stock <= bs.minimum_stock");
    }

    const [stocks] = await db.query(`
      SELECT
        bs.id,
        bs.lab_id,
        l.name AS laboratory_name,
        l.code AS laboratory_code,
        bs.item_catalog_id,
        ic.name AS item_name,
        ic.unit AS catalog_unit,
        bs.unit,
        bs.current_stock,
        bs.minimum_stock,
        CASE
          WHEN bs.current_stock <= 0 THEN 'habis'
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

    return stocks;
  },

  async findStocksReadonly({ labIds = null, labId = null, search = null, stockStatus = null } = {}) {
    const conditions = ["ic.type = 'bhp'"];
    const params = [];

    if (Array.isArray(labIds) && labIds.length > 0) {
      conditions.push("bs.lab_id IN (?)");
      params.push(labIds);
    }

    if (labId) {
      conditions.push("bs.lab_id = ?");
      params.push(Number(labId));
    }

    if (search) {
      conditions.push("(ic.name LIKE ? OR l.name LIKE ? OR l.code LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }

    if (stockStatus) {
      const statusMap = {
        habis:   "bs.current_stock <= 0",
        kritis:  "bs.current_stock > 0 AND bs.current_stock <= bs.minimum_stock",
        menipis: "bs.current_stock > bs.minimum_stock AND bs.current_stock <= (bs.minimum_stock * 2)",
        aman:    "bs.current_stock > (bs.minimum_stock * 2)",
      };
      const cond = statusMap[stockStatus];
      if (cond) conditions.push(cond);
    }

    const [stocks] = await db.query(`
      SELECT
        bs.id,
        bs.lab_id,
        l.name AS laboratory_name,
        l.code AS laboratory_code,
        bs.item_catalog_id,
        ic.name AS item_name,
        ic.unit  AS catalog_unit,
        bs.unit,
        bs.current_stock,
        bs.minimum_stock,
        CASE
          WHEN bs.current_stock <= 0 THEN 'habis'
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

    return stocks;
  },

  async findMovementsByStockId(stockId) {
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
    return movements;
  },

  async findStockById(id, tx = null) {
    const conn = tx || db;
    const [stocks] = await conn.query(`
      SELECT bs.id, bs.lab_id, bs.current_stock, bs.item_catalog_id, ic.name AS item_name
      FROM bhp_stocks bs
      JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
      WHERE bs.id = ?
      LIMIT 1
    `, [id]);
    return stocks[0] || null;
  },

  async findStockByIdForUpdate(id, tx = null) {
    const conn = tx || db;
    const [stocks] = await conn.query(`
      SELECT id, lab_id, current_stock
      FROM bhp_stocks
      WHERE id = ?
      LIMIT 1
      FOR UPDATE
    `, [id]);
    return stocks[0] || null;
  },

  async findStockByLabAndCatalog(labId, itemCatalogId, tx = null) {
    const conn = tx || db;
    const [stocks] = await conn.query(
      "SELECT id FROM bhp_stocks WHERE lab_id = ? AND item_catalog_id = ? LIMIT 1",
      [labId, itemCatalogId]
    );
    return stocks[0] || null;
  },

  async createCatalog({ name, unit }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO item_catalogs (category_id, name, type, unit, description)
      VALUES (NULL, ?, 'bhp', ?, NULL)
    `, [name, unit]);
    return result;
  },

  async findCatalogById(id, tx = null) {
    const conn = tx || db;
    const [catalogs] = await conn.query(
      "SELECT id, unit FROM item_catalogs WHERE id = ? AND type = 'bhp' LIMIT 1",
      [id]
    );
    return catalogs[0] || null;
  },

  async createStock({ labId, itemCatalogId, currentStock, minimumStock, unit }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit)
      VALUES (?, ?, ?, ?, ?)
    `, [labId, itemCatalogId, currentStock, minimumStock, unit]);
    return result;
  },

  async updateStock(id, { minimumStock, unit }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      UPDATE bhp_stocks
      SET minimum_stock = ?, unit = ?
      WHERE id = ?
    `, [minimumStock, unit, id]);
    return result;
  },

  async updateStockQty(id, currentStock, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(
      "UPDATE bhp_stocks SET current_stock = ? WHERE id = ?",
      [currentStock, id]
    );
    return result;
  },

  async createMovement({ stockId, performedBy, movementType, quantity, note, procurementItemId = null, receiptId = null, maintenanceId = null }, tx = null) {
    const conn = tx || db;
    const [result] = await conn.query(`
      INSERT INTO bhp_stock_movements (
        stock_id, procurement_item_id, receipt_id, maintenance_id,
        performed_by, movement_type, quantity, note
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `, [stockId, procurementItemId, receiptId, maintenanceId, performedBy, movementType, quantity, note || null]);
    return result;
  }
};

module.exports = BhpModel;