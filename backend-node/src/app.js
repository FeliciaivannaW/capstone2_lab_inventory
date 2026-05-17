const express = require("express");
const cors = require("cors");
const routes = require("./routes");

const app = express();

// Middleware
app.use(cors({
  origin: "http://localhost:8000",
  credentials: true
}));

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Request logging middleware (untuk debugging)
app.use((req, res, next) => {
  console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
  next();
});

// Routes
app.use("/api", routes);

app.get("/", (req, res) => {
  res.json({
    message: "Laboratory Inventory and BHP Management Backend API"
  });
});

// 404 Handler
app.use((req, res) => {
  res.status(404).json({
    status: "error",
    message: "Endpoint tidak ditemukan",
    path: req.path
  });
});

// Global Error Handler (PENTING!)
app.use((err, req, res, next) => {
  console.error("[ERROR]", {
    message: err.message,
    stack: err.stack,
    sql: err.sql,
    sqlState: err.sqlState
  });

  const statusCode = err.statusCode || 500;
  const message = err.message || "Terjadi kesalahan pada server";

  res.status(statusCode).json({
    status: "error",
    message: message,
    ...(process.env.NODE_ENV === "development" && { error: err.message })
  });
});

module.exports = app;
