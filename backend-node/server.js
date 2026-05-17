const app = require("./src/app");
require("dotenv").config();

const PORT = process.env.PORT || 3000;

// Validasi environment variables
if (!process.env.DB_HOST || !process.env.DB_USER || !process.env.DB_NAME) {
  console.error("[ERROR] Database configuration tidak lengkap di .env");
  process.exit(1);
}

if (!process.env.JWT_SECRET) {
  console.warn("[WARN] JWT_SECRET tidak dikonfigurasi, menggunakan default");
}

const server = app.listen(PORT, () => {
  console.log(`✓ Backend server berjalan di http://localhost:${PORT}`);
  console.log(`✓ Database: ${process.env.DB_NAME}@${process.env.DB_HOST}`);
});

// Handle unhandled promise rejections
process.on("unhandledRejection", (err) => {
  console.error("[UNHANDLED REJECTION]", err);
  server.close(() => process.exit(1));
});

// Handle uncaught exceptions
process.on("uncaughtException", (err) => {
  console.error("[UNCAUGHT EXCEPTION]", err);
  server.close(() => process.exit(1));
});
