const db = require("../config/database");
const LabAccessModel = require("../models/LabAccessModel");

const getSummary = async (req, res) => {
  try {
    let staffLabIds = null;
    let staffRoomIds = null;

    if (req.user?.role === "staf_laboratorium") {
      staffLabIds = await LabAccessModel.findAccessibleLabIds(req.user.id);
      staffRoomIds = await LabAccessModel.findAccessibleRoomIds(req.user.id);

      if (!staffLabIds.length) staffLabIds = [0];
      if (!staffRoomIds.length) staffRoomIds = [0];
    }

    const inventoryWhere = staffRoomIds ? "WHERE room_id IN (?)" : "";
    const labelWhere = staffRoomIds ? "WHERE room_id IN (?) AND status NOT IN ('disposed','replaced')" : "WHERE status NOT IN ('disposed','replaced')";
    const bhpWhere = staffLabIds ? "WHERE lab_id IN (?)" : "";
    const bhpLowWhere = staffLabIds
      ? "WHERE bs.lab_id IN (?) AND bs.current_stock <= bs.minimum_stock AND bs.minimum_stock > 0"
      : "WHERE bs.current_stock <= bs.minimum_stock AND bs.minimum_stock > 0";

    const inventoryParams = staffRoomIds ? [staffRoomIds] : [];
    const labelParams = staffRoomIds ? [staffRoomIds] : [];
    const bhpParams = staffLabIds ? [staffLabIds] : [];
    const bhpLowParams = staffLabIds ? [staffLabIds] : [];

    const [
      [inventoryStats],
      [labelStats],
      [bhpStats],
      [bhpLowStock],
      [procStats],
      [receptionStats],
      [monthlyTrend],
      [recentReceipts]
    ] = await Promise.all([

      // 1. Inventory kondisi & status
      db.query(`
        SELECT
          COUNT(*) AS total,
          SUM(CASE WHEN status IN ('available','in_use') THEN 1 ELSE 0 END)       AS active,
          SUM(CASE WHEN status = 'received'              THEN 1 ELSE 0 END)       AS received,
          SUM(CASE WHEN status = 'labeled'               THEN 1 ELSE 0 END)       AS labeled_status,
          SUM(CASE WHEN status = 'maintenance'           THEN 1 ELSE 0 END)       AS in_maintenance,
          SUM(CASE WHEN asset_condition = 'baik'         THEN 1 ELSE 0 END)       AS cond_baik,
          SUM(CASE WHEN asset_condition = 'rusak_ringan' THEN 1 ELSE 0 END)       AS cond_rusak_ringan,
          SUM(CASE WHEN asset_condition = 'rusak_berat'  THEN 1 ELSE 0 END)       AS cond_rusak_berat,
          SUM(CASE WHEN asset_condition = 'maintenance'  THEN 1 ELSE 0 END)       AS cond_maintenance
        FROM inventory_assets
        ${inventoryWhere}
      `, inventoryParams),

      // 2. Label stats
      db.query(`
        SELECT
          SUM(CASE WHEN label_number IS NOT NULL AND label_number != '' THEN 1 ELSE 0 END) AS labeled,
          SUM(CASE WHEN label_number IS NULL OR label_number = ''        THEN 1 ELSE 0 END) AS unlabeled
        FROM inventory_assets
        ${labelWhere}
      `, labelParams),

      // 3. BHP summary
      db.query(`
        SELECT
          COUNT(*)                                                                    AS total_items,
          COALESCE(SUM(current_stock), 0)                                            AS total_stock,
          SUM(CASE WHEN current_stock <= minimum_stock AND minimum_stock > 0 THEN 1 ELSE 0 END) AS low_stock_count
        FROM bhp_stocks
        ${bhpWhere}
      `, bhpParams),

      // 4. BHP low stock detail (top 5)
      db.query(`
        SELECT
          ic.name        AS item_name,
          bs.current_stock,
          bs.minimum_stock,
          bs.unit,
          l.name         AS lab_name
        FROM bhp_stocks bs
        JOIN item_catalogs ic  ON bs.item_catalog_id = ic.id
        JOIN laboratories l    ON bs.lab_id = l.id
        ${bhpLowWhere}
        ORDER BY (bs.current_stock / GREATEST(bs.minimum_stock, 1)) ASC
        LIMIT 5
      `, bhpLowParams),

      // 5. Procurement pipeline
      db.query(`
        SELECT
          SUM(CASE WHEN status = 'draft'     THEN 1 ELSE 0 END) AS draft_count,
          SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
          SUM(CASE WHEN status = 'finalized' THEN 1 ELSE 0 END) AS finalized_count,
          SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) AS rejected_count,
          COUNT(*)                                               AS total
        FROM procurement_drafts
      `),

      // 6. Penerimaan: pending vs received
      db.query(`
        SELECT
          COUNT(DISTINCT pi.id)                                                             AS total_approved_items,
          SUM(CASE WHEN COALESCE(gr_sum.qty_received, 0) >= pi.quantity THEN 1 ELSE 0 END) AS fully_received,
          SUM(CASE WHEN COALESCE(gr_sum.qty_received, 0) = 0            THEN 1 ELSE 0 END) AS not_started,
          SUM(CASE WHEN COALESCE(gr_sum.qty_received, 0) > 0
                    AND COALESCE(gr_sum.qty_received, 0) < pi.quantity  THEN 1 ELSE 0 END) AS partial
        FROM procurement_items pi
        JOIN procurement_drafts pd ON pi.draft_id = pd.id
        LEFT JOIN (
          SELECT procurement_item_id, SUM(quantity_received) AS qty_received
          FROM goods_receipts
          GROUP BY procurement_item_id
        ) gr_sum ON pi.id = gr_sum.procurement_item_id
        WHERE pi.review_status = 'approved' AND pd.status = 'finalized'
      `),

      // 7. Trend penerimaan 6 bulan terakhir
      db.query(`
        SELECT
          DATE_FORMAT(received_date, '%Y-%m') AS month,
          DATE_FORMAT(received_date, '%b %Y') AS month_label,
          COUNT(*)                            AS receipt_count,
          SUM(quantity_received)              AS quantity_total
        FROM goods_receipts
        WHERE received_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY month, month_label
        ORDER BY month ASC
      `),

      // 8. Aktivitas terbaru (5 penerimaan)
      db.query(`
        SELECT
          gr.id,
          gr.received_date,
          gr.quantity_received,
          COALESCE(ic.name, pi.item_name) AS item_name,
          pi.item_type,
          pd.title                         AS draft_title,
          l.name                           AS lab_name,
          u.name                           AS received_by
        FROM goods_receipts gr
        JOIN procurement_items pi  ON gr.procurement_item_id = pi.id
        JOIN procurement_drafts pd ON pi.draft_id = pd.id
        JOIN laboratories l        ON pd.lab_id = l.id
        LEFT JOIN item_catalogs ic ON pi.item_catalog_id = ic.id
        JOIN users u               ON gr.received_by = u.id
        ORDER BY gr.created_at DESC
        LIMIT 5
      `)
    ]);

    res.json({
      status: "success",
      data: {
        inventory:     inventoryStats[0] || {},
        label:         labelStats[0] || {},
        bhp:           bhpStats[0] || {},
        bhpLowStock:   bhpLowStock || [],
        procurement:   procStats[0] || {},
        reception:     receptionStats[0] || {},
        monthlyTrend:  monthlyTrend || [],
        recentActivity: recentReceipts || []
      }
    });
  } catch (error) {
    console.error("[STATISTICS ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data statistik",
      detail: error.message
    });
  }
};

module.exports = { getSummary };
