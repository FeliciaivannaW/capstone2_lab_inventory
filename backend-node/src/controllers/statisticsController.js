const db = require("../config/database");

/**
 * Get summary statistics for staf administrasi dashboard
 * Aggregates data from inventory, BHP, procurement, and maintenance
 */
const getSummary = async (req, res, next) => {
  try {
    // 1. Inventory statistics
    const [inventoryStats] = await db.query(`
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'available' OR status = 'in_use' THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) AS in_maintenance,
        SUM(CASE WHEN status = 'disposed' THEN 1 ELSE 0 END) AS disposed,
        SUM(CASE WHEN status = 'replaced' THEN 1 ELSE 0 END) AS replaced,
        SUM(CASE WHEN asset_condition = 'baik' THEN 1 ELSE 0 END) AS condition_good,
        SUM(CASE WHEN asset_condition = 'rusak_ringan' THEN 1 ELSE 0 END) AS condition_light_damage,
        SUM(CASE WHEN asset_condition = 'rusak_berat' THEN 1 ELSE 0 END) AS condition_heavy_damage,
        SUM(CASE WHEN label_number IS NOT NULL AND label_number != '' THEN 1 ELSE 0 END) AS labeled,
        SUM(CASE WHEN label_number IS NULL OR label_number = '' THEN 1 ELSE 0 END) AS unlabeled
      FROM inventory_assets
    `);

    // 2. BHP stock statistics
    const [bhpStats] = await db.query(`
      SELECT
        COUNT(*) AS total_items,
        SUM(current_stock) AS total_stock,
        SUM(CASE WHEN current_stock <= minimum_stock THEN 1 ELSE 0 END) AS low_stock_count
      FROM bhp_stocks
    `);

    // 3. BHP stock by category
    const [bhpByCategory] = await db.query(`
      SELECT
        icat.name AS category_name,
        COUNT(bs.id) AS item_count,
        SUM(bs.current_stock) AS total_stock,
        SUM(CASE WHEN bs.current_stock <= bs.minimum_stock THEN 1 ELSE 0 END) AS low_stock
      FROM bhp_stocks AS bs
      JOIN item_catalogs AS ic ON bs.item_catalog_id = ic.id
      LEFT JOIN item_categories AS icat ON ic.category_id = icat.id
      GROUP BY icat.id, icat.name
    `);

    // 4. Procurement statistics
    const [procurementStats] = await db.query(`
      SELECT
        COUNT(*) AS total_drafts,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_count,
        SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
        SUM(CASE WHEN status = 'finalized' THEN 1 ELSE 0 END) AS finalized_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
      FROM procurement_drafts
    `);

    // 5. Procurement items reception status
    const [receptionStats] = await db.query(`
      SELECT
        COUNT(pi.id) AS total_approved_items,
        SUM(CASE WHEN gr.id IS NOT NULL THEN 1 ELSE 0 END) AS received_items,
        SUM(CASE WHEN gr.id IS NULL THEN 1 ELSE 0 END) AS pending_items
      FROM procurement_items AS pi
      JOIN procurement_drafts AS pd ON pi.draft_id = pd.id
      LEFT JOIN goods_receipts AS gr ON pi.id = gr.procurement_item_id
      WHERE pi.review_status = 'approved' AND pd.status = 'finalized'
    `);

    // 6. Maintenance statistics
    const [maintenanceStats] = await db.query(`
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'planned' THEN 1 ELSE 0 END) AS planned,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
      FROM maintenance_logs
    `);

    // 7. Recent activity (last 10 events)
    const [recentReceipts] = await db.query(`
      SELECT
        'receipt' AS type,
        CONCAT('Penerimaan: ', pi.item_name) AS description,
        gr.received_date AS event_date,
        u.name AS user_name
      FROM goods_receipts AS gr
      JOIN procurement_items AS pi ON gr.procurement_item_id = pi.id
      JOIN users AS u ON gr.received_by = u.id
      ORDER BY gr.created_at DESC
      LIMIT 10
    `);

    res.json({
      status: "success",
      data: {
        inventory: inventoryStats[0] || {},
        bhp: bhpStats[0] || {},
        bhpByCategory: bhpByCategory || [],
        procurement: procurementStats[0] || {},
        reception: receptionStats[0] || {},
        maintenance: maintenanceStats[0] || {},
        recentActivity: recentReceipts || []
      }
    });
  } catch (error) {
    console.error("[STATISTICS ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data statistik",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  getSummary
};
