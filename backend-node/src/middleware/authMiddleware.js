const jwt = require("jsonwebtoken");

const authMiddleware = (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;

    if (!authHeader || !authHeader.startsWith("Bearer ")) {
      return res.status(401).json({
        status: "error",
        message: "Token tidak ditemukan"
      });
    }

    if (!process.env.JWT_SECRET) {
      console.error("[AUTH MIDDLEWARE ERROR] JWT_SECRET tidak dikonfigurasi");
      return res.status(500).json({
        status: "error",
        message: "Konfigurasi server tidak lengkap"
      });
    }

    const token = authHeader.split(" ")[1];
    const decoded = jwt.verify(token, process.env.JWT_SECRET);

    req.user = decoded;
    next();
  } catch (error) {
    console.error("[AUTH MIDDLEWARE ERROR]", error.message);
    return res.status(401).json({
      status: "error",
      message: "Token tidak valid atau sudah expired",
      detail: error.message
    });
  }
};

module.exports = authMiddleware;