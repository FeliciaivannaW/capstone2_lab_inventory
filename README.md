# Labventory

## Laboratory Asset Management Application

Labventory is a full-stack system for digitizing laboratory inventory, managing consumables, and tracking the item lifecycle from procurement to disposal.

---

## 📋 Table of Contents

1. [Project Information](#1-project-information)
2. [Tech Stack](#2-tech-stack)
3. [Role-Based Access Control](#3-role-based-access-control)
4. [Workflow and Environment](#4-workflow-and-environment)
5. [Team Information](#5-team-information)

---

## 1. Project Information

This application was built to help laboratories digitize the recording of assets and consumables. The system also provides a clear workflow for submitting annual item procurement and tracking item status periodically.

Through this system, laboratory items can be monitored from the moment they are purchased, maintained, replaced, until they are finally removed from the system.

### ✨ Key Features

| Feature | Description |
|---|---|
| 📦 Asset Digitalization | Digital recording of laboratory assets and consumables in one centralized system |
| 🛒 Item Procurement | Annual item purchasing draft submission system with purchase reference links |
| 🔄 Lifecycle Tracking | Tracking item history from procurement, maintenance, replacement, to disposal |
| 🏷️ Inventory Update | Label numbering and QR/Barcode photo allocation for each asset |
| 📉 Stock Management | Automatic deduction of consumable stock when items are used during maintenance |

---

## 2. Tech Stack

### 🔧 Core Framework

| Layer | Technology |
|---|---|
| Frontend | Laravel Blade |
| Backend | Node.js |
| Database | MySQL |

---

## 3. Role-Based Access Control

This system implements role-based access control for five different user roles. Each role has its own responsibilities and access limitations.

### 👥 User Roles

| Role | Description |
|---|---|
| 🛡️ Administrator | Manages all user data within the system and manages laboratory room data |
| 🧪 Head of Laboratory | Creates annual procurement drafts containing a list of inventory items to buy, marks old items to be replaced, and views draft history |
| 🎓 Head of Study Program | Reviews drafts from the Head of Laboratory, selects which items are approved or rejected, and finalizes the draft so the data is locked |
| 📋 Administrative Staff | Views drafts approved by the Head of Study Program, updates inventory with label numbers and QR/Barcode, and inputs item arrival dates |
| 🔧 Laboratory Staff | Manages daily consumable stock, logs item maintenance, and updates asset conditions. The system automatically reduces consumable stock if items are used for maintenance |

---

## 4. Workflow and Environment

### a. Quick-Start Guide

Before running this project locally, make sure the following software is installed on your computer:

- PHP
- Composer
- Node.js
- MySQL

### How to Run the Project Locally

1. Clone this repository to your computer.

2. Open a terminal in the backend folder, then run:

   ```bash
   npm install
   ```

3. Open a terminal in the frontend folder, then run:

   ```bash
   composer install
   ```

4. Copy the `.env.example` file and rename it to `.env`.

5. Adjust the MySQL database configuration and backend URL inside the `.env` file.

6. Run the database migration:

   ```bash
   php artisan migrate
   ```

7. Start the backend API server:

   ```bash
   node server.js
   ```

8. Start the Laravel frontend server:

   ```bash
   php artisan serve
   ```

9. Open the application in your browser using the URL shown in the terminal.

---

## 5. Team Information
- Febrian Timotius Sugiarto - 2472039 - Project Leader
- Miracle Steven Gerrald - 2472019 - Team Member
- Felicia Ivanna Widian - 2472030 - Team Member

---
