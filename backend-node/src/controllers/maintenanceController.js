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

const getMaintenanceLogs = async (req, res) => {
  try {
    const currentUser = await getCurrentUser(req.user.id);
    const { asset_id, status, search } = req.query;
    const conditions = [];
    const params = [];

    if (currentUser.role === "staf_laboratorium") {
      if (!currentUser.lab_id) {
        return res.status(400).json({ status: "error", message: "User staf laboratorium belum terhubung ke laboratorium" });
      }
      conditions.push("COALESCE(lproc.id, lroom.id) = ?");
      params.push(currentUser.lab_id);
    }

    if (asset_id) {
      conditions.push("ml.inventory_asset_id = ?");
      params.push(Number(asset_id));
    }

    if (status) {
      conditions.push("ml.status = ?");
      params.push(status);
    }

    if (search) {
      conditions.push("(ia.asset_code LIKE ? OR ia.label_number LIKE ? OR ic.name LIKE ? OR ml.issue_description LIKE ? OR ml.action_taken LIKE ?)");
      params.push(`%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`);
    }

    const whereClause = conditions.length ? `WHERE ${conditions.join(" AND ")}` : "";

    const [logs] = await db.query(`
      SELECT
        ml.id,
        ml.inventory_asset_id,
        ia.asset_code,
        ia.label_number,
        ic.name AS item_name,
        r.name AS room_name,
        COALESCE(lproc.id, lroom.id) AS lab_id,
        COALESCE(lproc.name, lroom.name) AS laboratory_name,
        u.name AS performed_by_name,
        ml.maintenance_date,
        ml.issue_description,
        ml.action_taken,
        ml.condition_before,
        ml.condition_after,
        ml.status,
        ml.cost,
        ml.notes,
        ml.created_at,
        ml.updated_at
      FROM maintenance_logs ml
      JOIN inventory_assets ia ON ml.inventory_asset_id = ia.id
      JOIN item_catalogs ic ON ia.item_catalog_id = ic.id
      LEFT JOIN rooms r ON ia.room_id = r.id
      LEFT JOIN laboratories lroom ON r.id = lroom.room_id
      LEFT JOIN procurement_items pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories lproc ON pd.lab_id = lproc.id
      JOIN users u ON ml.performed_by = u.id
      ${whereClause}
      ORDER BY ml.maintenance_date DESC, ml.id DESC
    `, params);

    if (!logs.length) {
      return res.json({ status: "success", data: [] });
    }

    const ids = logs.map((log) => log.id);
    const [usageRows] = await db.query(`
      SELECT
        m.maintenance_id,
        m.stock_id,
        ic.name AS item_name,
        bs.unit,
        m.quantity,
        m.note
      FROM bhp_stock_movements m
      JOIN bhp_stocks bs ON m.stock_id = bs.id
      JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
      WHERE m.maintenance_id IN (?) AND m.movement_type = 'maintenance_usage'
      ORDER BY m.id ASC
    `, [ids]);

    const usageMap = usageRows.reduce((acc, row) => {
      if (!acc[row.maintenance_id]) acc[row.maintenance_id] = [];
      acc[row.maintenance_id].push(row);
      return acc;
    }, {});

    const result = logs.map((log) => ({
      ...log,
      bhp_usages: usageMap[log.id] || []
    }));

    res.json({ status: "success", data: result });
  } catch (error) {
    console.error("[GET MAINTENANCE ERROR]", error);
    res.status(500).json({ status: "error", message: "Gagal mengambil log maintenance" });
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

    let bhpUsages = req.body.bhp_usages || [];
    if (typeof bhpUsages === "string") {
      try {
        bhpUsages = JSON.parse(bhpUsages);
      } catch (_) {
        bhpUsages = [];
      }
    }

    if (!inventory_asset_id || !maintenance_date || !condition_after) {
      return res.status(400).json({ status: "error", message: "Aset, tanggal maintenance, dan kondisi akhir wajib diisi" });
    }

    if (!["planned", "in_progress", "done", "cancelled"].includes(status)) {
      return res.status(400).json({ status: "error", message: "Status maintenance tidak valid" });
    }

    const validConditions = ["baik", "rusak_ringan", "rusak_berat", "maintenance", "dihapus", "diganti"];
    if (!validConditions.includes(condition_after)) {
      return res.status(400).json({ status: "error", message: "Kondisi akhir tidak valid" });
    }

    const currentUser = await getCurrentUser(req.user.id);

    await connection.beginTransaction();

    const [assets] = await connection.query(`
      SELECT ia.id, ia.asset_condition, ia.status, ia.room_id, COALESCE(lproc.id, lroom.id) AS lab_id
      FROM inventory_assets ia
      LEFT JOIN rooms r ON ia.room_id = r.id
      LEFT JOIN laboratories lroom ON r.id = lroom.room_id
      LEFT JOIN procurement_items pi ON ia.procurement_item_id = pi.id
      LEFT JOIN procurement_drafts pd ON pi.draft_id = pd.id
      LEFT JOIN laboratories lproc ON pd.lab_id = lproc.id
      WHERE ia.id = ?
      LIMIT 1
      FOR UPDATE
    `, [Number(inventory_asset_id)]);

    if (!assets.length) {
      throw Object.assign(new Error("Aset inventaris tidak ditemukan"), { statusCode: 404 });
    }

    const asset = assets[0];
    if (currentUser.role === "staf_laboratorium" && currentUser.lab_id && asset.lab_id && asset.lab_id !== currentUser.lab_id) {
      throw Object.assign(new Error("Tidak boleh maintenance aset dari lab lain"), { statusCode: 403 });
    }

    const [result] = await connection.query(`
      INSERT INTO maintenance_logs (
        inventory_asset_id,
        performed_by,
        maintenance_date,
        issue_description,
        action_taken,
        condition_before,
        condition_after,
        status,
        cost,
        notes
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [
      Number(inventory_asset_id),
      req.user.id,
      maintenance_date,
      issue_description || null,
      action_taken || null,
      asset.asset_condition,
      condition_after,
      status,
      Number(cost) || 0,
      notes
    ]);

    const maintenanceId = result.insertId;

    if (status !== "cancelled" && condition_after !== asset.asset_condition) {
      const nextStatus = condition_after === "maintenance" || status === "in_progress"
        ? "maintenance"
        : (asset.status === "received" ? "received" : "available");

      await connection.query(`
        UPDATE inventory_assets
        SET asset_condition = ?, status = ?
        WHERE id = ?
      `, [condition_after, nextStatus, Number(inventory_asset_id)]);

      await connection.query(`
        INSERT INTO asset_condition_logs (inventory_asset_id, updated_by, old_condition, new_condition, note)
        VALUES (?, ?, ?, ?, ?)
      `, [Number(inventory_asset_id), req.user.id, asset.asset_condition, condition_after, `Update dari maintenance #${maintenanceId}`]);
    }

    for (const usage of bhpUsages) {
      if (!usage || !usage.stock_id || !usage.quantity) continue;
      const stockId = Number(usage.stock_id);
      const qty = Number(usage.quantity);

      if (!Number.isInteger(stockId) || stockId <= 0 || !Number.isInteger(qty) || qty <= 0) {
        throw Object.assign(new Error("Data pemakaian BHP tidak valid"), { statusCode: 400 });
      }

      const [stocks] = await connection.query(`
        SELECT id, lab_id, current_stock
        FROM bhp_stocks
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
      `, [stockId]);

      if (!stocks.length) {
        throw Object.assign(new Error("Stok BHP tidak ditemukan"), { statusCode: 404 });
      }

      const stock = stocks[0];
      if (currentUser.role === "staf_laboratorium" && currentUser.lab_id && stock.lab_id !== currentUser.lab_id) {
        throw Object.assign(new Error("Tidak boleh memakai stok BHP dari lab lain"), { statusCode: 403 });
      }

      if (Number(stock.current_stock) < qty) {
        throw Object.assign(new Error("Stok BHP tidak cukup untuk maintenance"), { statusCode: 400 });
      }

      await connection.query("UPDATE bhp_stocks SET current_stock = current_stock - ? WHERE id = ?", [qty, stockId]);
      await connection.query(`
        INSERT INTO bhp_stock_movements (stock_id, maintenance_id, performed_by, movement_type, quantity, note)
        VALUES (?, ?, ?, 'maintenance_usage', ?, ?)
      `, [stockId, maintenanceId, req.user.id, qty, usage.note || `Pemakaian untuk maintenance #${maintenanceId}`]);
    }

    await connection.commit();
    res.status(201).json({ status: "success", message: "Log maintenance berhasil disimpan", data: { id: maintenanceId } });
  } catch (error) {
    await connection.rollback();
    console.error("[CREATE MAINTENANCE ERROR]", error);
    res.status(error.statusCode || 500).json({ status: "error", message: error.message || "Gagal menyimpan log maintenance" });
  } finally {
    connection.release();
  }
};

module.exports = {
  getMaintenanceLogs,
  createMaintenanceLog
};