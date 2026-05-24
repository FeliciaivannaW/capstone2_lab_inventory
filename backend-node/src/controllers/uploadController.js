const path = require("path");
const fs   = require("fs");

/**
 * POST /upload/asset-photo
 * Accepts multipart/form-data with field name: photo
 * Returns { photo_url } pointing to /uploads/assets/filename
 * Max size: 2MB | Allowed: jpg, jpeg, png, webp
 * Accessible to: staf_administrasi
 */
const uploadAssetPhoto = async (req, res, next) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        message: "File foto tidak ditemukan. Gunakan field name 'photo'.",
        errors:  { photo: "Wajib ada file" }
      });
    }

    // Ensure uploads directory exists
    const uploadsDir = path.join(__dirname, "../../uploads/assets");
    if (!fs.existsSync(uploadsDir)) {
      fs.mkdirSync(uploadsDir, { recursive: true });
    }

    const photo_url = `/uploads/assets/${req.file.filename}`;

    res.status(201).json({
      success:   true,
      message:   "Foto berhasil diupload",
      data:      { photo_url }
    });
  } catch (error) {
    console.error("[UPLOAD ASSET PHOTO ERROR]", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengupload foto",
      errors:  { detail: error.message }
    });
  }
};

module.exports = { uploadAssetPhoto };
