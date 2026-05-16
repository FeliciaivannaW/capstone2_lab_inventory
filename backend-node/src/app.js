const express = require("express");
const cors = require("cors");
const routes = require("./routes");

const app = express();

app.use(cors({
  origin: "http://localhost:8000",
  credentials: true
}));

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use("/api", routes);

app.get("/", (req, res) => {
  res.json({
    message: "Laboratory Inventory and BHP Management Backend API"
  });
});

module.exports = app;
