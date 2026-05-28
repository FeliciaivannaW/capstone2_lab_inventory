const AdminOverviewModel = require("../models/AdminOverviewModel");

const getAdminOverview = async (req, res) => {
  try {
    const [
      summary,
      assetsByCondition,
      bhpLowStocks,
      maintenanceSummary,
      roomSummary
    ] = await Promise.all([
      AdminOverviewModel.getSummary(),
      AdminOverviewModel.getAssetsByCondition(),
      AdminOverviewModel.getLowBhpStocks(),
      AdminOverviewModel.getMaintenanceSummary(),
      AdminOverviewModel.getRoomSummary()
    ]);

    res.json({
      status: "success",
      data: {
        summary,
        assets_by_condition: assetsByCondition,
        low_bhp_stocks: bhpLowStocks,
        maintenance_summary: maintenanceSummary,
        room_summary: roomSummary
      }
    });
  } catch (error) {
    console.error("[ADMIN OVERVIEW ERROR]", error);

    res.status(500).json({
      status: "error",
      message: "Gagal mengambil overview admin"
    });
  }
};

module.exports = {
  getAdminOverview
};