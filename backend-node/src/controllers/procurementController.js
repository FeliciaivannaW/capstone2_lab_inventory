const UserModel = require("../models/UserModel");
const ProcurementModel = require("../models/ProcurementModel");

const getProcurementDrafts = async (req, res, next) => {
  try {
    const { status, budget_year, search, lab_id } = req.query;
    const userRole = req.user?.role;
    
    let exclude_status = null;
    if (userRole === 'ketua_program_studi') {
      exclude_status = 'draft';
      if (status === 'draft') {
        return res.json({ status: "success", data: [] });
      }
    }

    const drafts = await ProcurementModel.findDrafts({ status, budget_year, search, lab_id, exclude_status });

    res.json({
      status: "success",
      data: drafts
    });
  } catch (error) {
    console.error("[PROCUREMENT DRAFTS ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil data draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const getProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const draft = await ProcurementModel.findDraftById(id);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    const userRole = req.user?.role;
    if (userRole === 'ketua_program_studi' && draft.status === 'draft') {
      return res.status(403).json({
        status: "error",
        message: "Draf belum di-submit untuk direview"
      });
    }

    const items = await ProcurementModel.findItemsByDraftId(id);
    draft.items = items;

    res.json({
      status: "success",
      data: draft
    });
  } catch (error) {
    console.error("[PROCUREMENT DRAFT DETAIL ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengambil detail draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const reviewProcurementItem = async (req, res, next) => {
  try {
    const { draftId, itemId } = req.params;
    const { review_status, review_note } = req.body;
    const userId = req.user?.id;

    if (!['approved', 'rejected'].includes(review_status)) {
      return res.status(400).json({
        status: "error",
        message: "Status review harus 'approved' atau 'rejected'"
      });
    }

    const draft = await ProcurementModel.findDraftByIdForLock(draftId);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.is_locked || draft.status === 'finalized') {
      return res.status(403).json({
        status: "error",
        message: "Draf sudah terkunci, tidak bisa diubah"
      });
    }

    const item = await ProcurementModel.findItemByIdAndDraftId(itemId, draftId);

    if (!item) {
      return res.status(404).json({
        status: "error",
        message: "Item pengadaan tidak ditemukan"
      });
    }

    const reviewedAt = new Date();
    await ProcurementModel.updateItemReview(itemId, {
      reviewStatus: review_status,
      reviewNote: review_note,
      reviewedBy: userId,
      reviewedAt
    });

    res.json({
      status: "success",
      message: `Item berhasil di-${review_status === 'approved' ? 'setujui' : 'tolak'}`,
      data: {
        item_id: itemId,
        review_status,
        reviewed_at: reviewedAt
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT ITEM REVIEW ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal melakukan review item pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const finalizeProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const userId = req.user?.id;

    const draft = await ProcurementModel.findDraftByIdForLock(id);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.is_locked || draft.status === 'finalized') {
      return res.status(403).json({
        status: "error",
        message: "Draf sudah terkunci atau sudah difinalisasi"
      });
    }

    const finalizedAt = new Date();
    await ProcurementModel.finalizeDraft(id, {
      status: 'finalized',
      isLocked: true,
      finalizedBy: userId,
      finalizedAt
    });

    res.json({
      status: "success",
      message: "Draf pengadaan berhasil difinalisasi dan terkunci",
      data: {
        draft_id: id,
        status: 'finalized',
        is_locked: true,
        finalized_at: finalizedAt
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT FINALIZE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal menfinalisasi draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const returnProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const { return_note } = req.body;
    const userId = req.user?.id;

    const draft = await ProcurementModel.findDraftByIdForLock(id);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.is_locked || draft.status === 'finalized') {
      return res.status(403).json({
        status: "error",
        message: "Draf sudah terkunci atau sudah difinalisasi"
      });
    }

    // append note
    let newNotes = draft.notes || '';
    if (return_note) {
      const timestamp = new Date().toLocaleString('id-ID');
      newNotes = (newNotes ? newNotes + "\n\n" : "") + `[Catatan Revisi Kaprodi - ${timestamp}]:\n${return_note}`;
    }

    await ProcurementModel.returnDraft(id, newNotes);

    res.json({
      status: "success",
      message: "Draf pengadaan berhasil dikembalikan ke Kepala Lab",
      data: {
        draft_id: id,
        status: 'draft',
        notes: newNotes
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT RETURN ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal mengembalikan draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const createProcurementDraft = async (req, res, next) => {
  try {
    const { title, lab_id, budget_year, notes } = req.body;
    const userId = req.user?.id;
    const userRole = req.user?.role;

    if (!title || !lab_id || !budget_year) {
      return res.status(400).json({
        status: "error",
        message: "Field title, lab_id, dan budget_year harus diisi"
      });
    }

    const createdAt = new Date();
    const result = await ProcurementModel.createDraft({
      labId: lab_id,
      createdBy: userId,
      title,
      budgetYear: budget_year,
      notes,
      status: 'draft',
      createdAt
    });

    res.json({
      status: "success",
      message: "Draf pengadaan berhasil dibuat",
      data: {
        id: result.insertId,
        title,
        lab_id,
        budget_year,
        status: 'draft',
        is_locked: false,
        created_at: createdAt
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT CREATE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal membuat draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const updateProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const { title, lab_id, budget_year, notes } = req.body;
    const userId = req.user?.id;
    const userRole = req.user?.role;

    const draft = await ProcurementModel.findDraftByIdForLock(id);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.status !== 'draft' || draft.is_locked) {
      return res.status(403).json({
        status: "error",
        message: "Draf tidak bisa diubah dalam status ini"
      });
    }

    if (userRole !== 'staf_administrasi' && draft.created_by !== userId) {
      return res.status(403).json({
        status: "error",
        message: "Anda tidak memiliki wewenang untuk mengubah draf ini"
      });
    }

    await ProcurementModel.updateDraft(id, { title, labId: lab_id, budgetYear: budget_year, notes });

    res.json({
      status: "success",
      message: "Draf pengadaan berhasil diperbarui",
      data: { id, title, lab_id, budget_year }
    });
  } catch (error) {
    console.error("[PROCUREMENT UPDATE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal memperbarui draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const deleteProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const userId = req.user?.id;
    const userRole = req.user?.role;

    const draft = await ProcurementModel.findDraftByIdForLock(id);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.status !== 'draft' || draft.is_locked) {
      return res.status(403).json({
        status: "error",
        message: "Hanya draf berstatus 'draft' yang bisa dihapus"
      });
    }

    if (userRole !== 'staf_administrasi' && draft.created_by !== userId) {
      return res.status(403).json({
        status: "error",
        message: "Anda tidak memiliki wewenang untuk menghapus draf ini"
      });
    }

    // items count check removed to allow cascade deletion of drafts with items

    await ProcurementModel.deleteDraft(id);

    res.json({
      status: "success",
      message: "Draf pengadaan berhasil dihapus"
    });
  } catch (error) {
    console.error("[PROCUREMENT DELETE ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal menghapus draf pengadaan",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const addProcurementItem = async (req, res, next) => {
  try {
    const { id: draftId } = req.params;
    const { item_name, item_type, quantity, estimated_price, purchase_link, replacement_asset_id } = req.body;
    const userId = req.user?.id;
    const userRole = req.user?.role;

    const draft = await ProcurementModel.findDraftByIdForLock(draftId);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.status !== 'draft' || draft.is_locked) {
      return res.status(403).json({
        status: "error",
        message: "Tidak bisa menambah item ke draf ini"
      });
    }

    if (userRole !== 'staf_administrasi' && draft.created_by !== userId) {
      return res.status(403).json({
        status: "error",
        message: "Anda tidak memiliki wewenang untuk mengubah draf ini"
      });
    }

    if (!item_name || !item_type || !quantity || estimated_price === undefined) {
      return res.status(400).json({
        status: "error",
        message: "Field item_name, item_type, quantity, dan estimated_price harus diisi"
      });
    }

    const createdAt = new Date();
    await ProcurementModel.createItem({
      draftId,
      itemName: item_name,
      itemType: item_type,
      quantity,
      estimatedPrice: estimated_price,
      purchaseLink: purchase_link,
      replacementAssetId: replacement_asset_id,
      createdAt
    });

    res.json({
      status: "success",
      message: "Item berhasil ditambahkan ke draf",
      data: {
        item_name,
        item_type,
        quantity,
        estimated_price,
        review_status: 'pending',
        created_at: createdAt
      }
    });
  } catch (error) {
    console.error("[PROCUREMENT ADD ITEM ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal menambahkan item",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const deleteProcurementItem = async (req, res, next) => {
  try {
    const { id: draftId, itemId } = req.params;
    const userId = req.user?.id;
    const userRole = req.user?.role;

    const draft = await ProcurementModel.findDraftByIdForLock(draftId);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.status !== 'draft') {
      return res.status(403).json({
        status: "error",
        message: "Tidak bisa menghapus item dari draf ini"
      });
    }

    if (userRole !== 'staf_administrasi' && draft.created_by !== userId) {
      return res.status(403).json({
        status: "error",
        message: "Anda tidak memiliki wewenang"
      });
    }

    const item = await ProcurementModel.findItemByIdAndDraftId(itemId, draftId);

    if (!item) {
      return res.status(404).json({
        status: "error",
        message: "Item tidak ditemukan"
      });
    }

    if (item.review_status !== 'pending') {
      return res.status(403).json({
        status: "error",
        message: "Hanya item dengan status pending yang bisa dihapus"
      });
    }

    await ProcurementModel.deleteItem(itemId);

    res.json({
      status: "success",
      message: "Item berhasil dihapus"
    });
  } catch (error) {
    console.error("[PROCUREMENT DELETE ITEM ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal menghapus item",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

const submitProcurementDraft = async (req, res, next) => {
  try {
    const { id } = req.params;
    const userId  = req.user?.id;

    const draft = await ProcurementModel.findDraftByIdForLock(id);

    if (!draft) {
      return res.status(404).json({ success: false, message: "Draf pengadaan tidak ditemukan" });
    }

    if (draft.is_locked) {
      return res.status(403).json({ success: false, message: "Draf sudah terkunci, tidak bisa diubah" });
    }

    if (draft.status !== 'draft') {
      return res.status(400).json({ success: false, message: `Draf sudah dalam status '${draft.status}', tidak bisa di-submit ulang` });
    }

    const count = await ProcurementModel.countItemsInDraft(id);
    if (count < 1) {
      return res.status(400).json({ success: false, message: "Draf harus memiliki minimal 1 item sebelum di-submit" });
    }

    const submittedAt = new Date();
    await ProcurementModel.submitDraft(id, { status: 'submitted', submittedAt });

    res.json({
      success: true,
      message: "Draf berhasil di-submit untuk review Kaprodi",
      data:    { draft_id: id, status: 'submitted', submittedAt }
    });
  } catch (error) {
    console.error("[PROCUREMENT SUBMIT ERROR]", error);
    res.status(500).json({ success: false, message: "Gagal submit draf", errors: { detail: error.message } });
  }
};

const updateProcurementItem = async (req, res, next) => {
  try {
    const { id: draftId, itemId } = req.params;
    const { item_name, item_type, quantity, estimated_price, purchase_link, replacement_asset_id } = req.body;
    const userId   = req.user?.id;
    const userRole = req.user?.role;

    const draft = await ProcurementModel.findDraftByIdForLock(draftId);
    if (!draft) {
      return res.status(404).json({ success: false, message: "Draf tidak ditemukan" });
    }

    if (draft.is_locked || draft.status !== 'draft') {
      return res.status(403).json({ success: false, message: "Draf tidak bisa diedit dalam status ini" });
    }
    if (userRole !== 'staf_administrasi' && draft.created_by !== userId) {
      return res.status(403).json({ success: false, message: "Tidak memiliki wewenang untuk mengubah item ini" });
    }

    const item = await ProcurementModel.findItemByIdAndDraftId(itemId, draftId);
    if (!item) {
      return res.status(404).json({ success: false, message: "Item tidak ditemukan dalam draf ini" });
    }

    const fields = {};
    if (item_name)        fields.item_name = item_name;
    if (item_type)        fields.item_type = item_type;
    if (quantity)         fields.quantity = quantity;
    if (estimated_price !== undefined) fields.estimated_price = estimated_price;
    if (purchase_link !== undefined)   fields.purchase_link = purchase_link || null;
    if (replacement_asset_id !== undefined) fields.replacement_asset_id = replacement_asset_id || null;

    if (Object.keys(fields).length === 0) {
      return res.status(400).json({ success: false, message: "Tidak ada field yang diubah" });
    }

    await ProcurementModel.updateItem(itemId, fields);

    res.json({ success: true, message: "Item berhasil diperbarui", data: { item_id: itemId } });
  } catch (error) {
    console.error("[PROCUREMENT UPDATE ITEM ERROR]", error);
    res.status(500).json({ success: false, message: "Gagal memperbarui item", errors: { detail: error.message } });
  }
};

const syncProcurementItems = async (req, res, next) => {
  try {
    const { id: draftId } = req.params;
    const { items } = req.body;
    const userId = req.user?.id;
    const userRole = req.user?.role;

    const draft = await ProcurementModel.findDraftByIdForLock(draftId);

    if (!draft) {
      return res.status(404).json({
        status: "error",
        message: "Draf pengadaan tidak ditemukan"
      });
    }

    if (draft.status !== 'draft' || draft.is_locked) {
      return res.status(403).json({
        status: "error",
        message: "Tidak bisa mengubah item draf dalam status ini"
      });
    }

    if (userRole !== 'staf_administrasi' && draft.created_by !== userId) {
      return res.status(403).json({
        status: "error",
        message: "Anda tidak memiliki wewenang untuk mengubah draf ini"
      });
    }

    await ProcurementModel.syncItems(draftId, items || []);

    res.json({
      status: "success",
      message: "Item draf pengadaan berhasil disinkronisasi"
    });
  } catch (error) {
    console.error("[PROCUREMENT SYNC ITEMS ERROR]", error);
    res.status(500).json({
      status: "error",
      message: "Gagal menyinkronisasi item draf",
      detail: error.message || "Kesalahan tidak diketahui"
    });
  }
};

module.exports = {
  getProcurementDrafts,
  getProcurementDraft,
  reviewProcurementItem,
  finalizeProcurementDraft,
  submitProcurementDraft,
  createProcurementDraft,
  updateProcurementDraft,
  deleteProcurementDraft,
  addProcurementItem,
  updateProcurementItem,
  deleteProcurementItem,
  syncProcurementItems,
  returnProcurementDraft
};
