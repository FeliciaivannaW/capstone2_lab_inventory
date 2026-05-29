# Labventory 🖥️🧪

## Laboratory Asset Management Application

Labventory is a full-stack system for digitizing laboratory inventory, managing consumables, and tracking the item lifecycle from procurement to disposal.

---

## 📋 Table of Contents

1. [Project Information](#1-project-information)
2. [Tech Stack](#2-tech-stack)
3. [Role-Based Access Control](#3-role-based-access-control)
4. [Workflow and Environment](#4-workflow-and-environment)
5. [End-to-End Testing Workflow](#5-alur-testing-aplikasi-end-to-end-workflow)
6. [Team Information](#6-team-information)

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

### a. System Requirements

Before running this project, make sure the following software is installed:

- **Node.js** (v16 or higher)
- **npm** or **yarn**
- **PHP** (v8.1 or higher)
- **Composer**
- **MySQL** (or MariaDB)
- **Git**

### b. Quick Installation (Automated Setup - Windows & Linux/Mac)

#### **For Windows Users:**
1. Navigate to project root folder
2. Double-click `setup.bat`
3. Wait for setup to complete
4. The script will automatically launch 3 servers:
   - Backend: http://localhost:3000
   - Frontend: http://localhost:8000
   - Vite Dev Server: http://localhost:5173

#### **For Linux/Mac Users:**
1. Navigate to project root folder
2. Run: `bash setup.sh`
3. Wait for setup to complete
4. The script will automatically launch 3 servers

---

### c. Step-by-Step Manual Setup

If you prefer to set up manually or encounter issues with automated setup:

#### **Step 1: Database Setup**

```bash
# Using MySQL CLI
mysql -u root -p

# In MySQL prompt:
SOURCE path/to/database/schema.sql;
SOURCE path/to/database/seed.sql;
EXIT;
```

#### **Step 2: Backend Setup (Node.js)**

```bash
cd backend-node

# Install dependencies
npm install

# Create .env file (copy from .env.example if needed)
# Make sure database credentials match your MySQL setup
cat .env
# Check: DB_HOST=localhost, DB_NAME=lab_inventory_db, DB_USER=root

# Start backend server
npm run dev
```

The backend will run on: **http://localhost:3000**

Test it:
```bash
curl http://localhost:3000/api/health
```

#### **Step 3: Frontend Setup (Laravel + Vite)**

Open **another terminal** and run:

```bash
cd frontend-laravel

# Install PHP dependencies
composer install

# Create .env file
cp .env.example .env

# Update .env with your configuration
# Important: 
#   - APP_KEY= (will be generated)
#   - VITE_BACKEND_URL=http://localhost:3000

# Generate APP_KEY
php artisan key:generate

# Install Node.js dependencies for Vite
npm install

# Start Laravel development server
php artisan serve
```

The Laravel server will run on: **http://localhost:8000**

#### **Step 4: Start Vite Dev Server**

Open **another terminal** and run:

```bash
cd frontend-laravel

# Start Vite for asset compilation and hot reload
npm run dev
```

The Vite server will run on: **http://localhost:5173** (for hot reload reference)

---

### d. Accessing the Application

Once all servers are running:

1. **Open browser** → http://localhost:8000
2. **Login with test credentials:**
   - Email: `admin@example.com`
   - Password: `password123`

---

### e. Test API Endpoints

#### Health Check:
```bash
curl http://localhost:3000/api/health
```

#### Get All Roles:
```bash
curl http://localhost:3000/api/roles
```

#### Login:
```bash
curl -X POST http://localhost:3000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

---

### f. Troubleshooting

| Issue | Solution |
|-------|----------|
| **Port 3000 already in use** | Change backend port in `.env`: `PORT=3001` |
| **Port 8000 already in use** | Change frontend port: `php artisan serve --port=8001` |
| **Port 5173 already in use** | Vite will auto-use next available port |
| **MySQL connection failed** | Check `.env` database credentials and MySQL is running |
| **404 on frontend routes** | Ensure both backend and frontend servers are running |
| **CORS errors** | Check backend `src/app.js` CORS configuration |
| **Assets not loading** | Ensure Vite dev server (`npm run dev` in frontend) is running |

---

### g. Environment Variables Reference

#### Backend `.env` (backend-node/.env):
```
PORT=3000
DB_HOST=localhost
DB_PORT=3306
DB_NAME=lab_inventory_db
DB_USER=root
DB_PASSWORD=
JWT_SECRET=capstone_lab_inventory_secret
```

#### Frontend `.env` (frontend-laravel/.env):
```
APP_NAME=Lab Inventory System
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
SESSION_DRIVER=database

VITE_BACKEND_URL=http://localhost:3000
VITE_API_URL=http://localhost:3000/api
```

---

### h. Default Test Users

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password123 | Administrator |
| kalab@example.com | password123 | Head of Laboratory |
| kaprodi@example.com | password123 | Head of Study Program |
| stafadmin@example.com | password123 | Administrative Staff |
| staflab@example.com | password123 | Laboratory Staff |

⚠️ **For Production:** Hash all passwords with bcrypt instead of storing plain text.

---

## 5. End-to-End Testing Workflow

To test all the features of the **Labventory** system sequentially from start to finish, you can follow this cross-role testing workflow scenario:

### 1. Phase 1: Creating Procurement Drafts (Role: Head of Laboratory)
* **Login:** `kalab@example.com` / `password123`
* **Testing Steps:**
  1. Go to the **Draf Pengadaan** (Procurement Draft) menu.
  2. Create a new procurement draft by filling in details such as draft name, budget year, and description.
  3. Enter the detail page of the newly created draft, and add some items (fill in item name, quantity, unit, estimated price, reference purchase link, and urgency).
  4. Once all items are added, click the **Ajukan Draf** (Submit Draft) button to submit the draft to the Head of Study Program. The draft status will change to `submitted`.

### 2. Phase 2: Reviewing & Approving Drafts (Role: Head of Study Program)
* **Login:** `kaprodi@example.com` / `password123`
* **Testing Steps:**
  1. Go to the **Draf Pengadaan** (Procurement Draft) menu. You will see the draft submitted by the Head of Laboratory with status `submitted`.
  2. Click the **Tinjau** (Review) button on that draft.
  3. Check each item in the table. You can choose whether the item is **Approved** or **Rejected** along with the reasons on the review form.
  4. Once all items have been reviewed, click the **Finalisasi Draf** (Finalize Draft) button. The draft status will change to `finalized` and the draft will be locked (cannot be edited again).

### 3. Phase 3: Receiving Goods & Labeling Assets (Role: Administrative Staff)
* **Login:** `stafadmin@example.com` / `password123`
* **Testing Steps:**
  1. Go to the **Penerimaan Barang** (Goods Receipt) menu. The draft with status `finalized` will appear here.
  2. When the physical items arrive, click **Terima Barang** (Receive Goods). Enter the receipt number, item arrival date, and upload a receipt invoice photo.
  3. Go to the **Update Label & Foto** (Update Label & Photo) menu.
  4. The system will automatically split the received procurement items into individual asset units. Enter the **Label Number** of the physical asset and upload a **QR/Barcode Photo** for each unit of the asset to officially register it into the system. Once saved, the asset status will automatically be updated to `active`.

### 4. Phase 4: Asset Maintenance & Consumables Usage (Role: Laboratory Staff)
* **Login:** `staflab@example.com` / `password123`
* **Testing Steps:**
  1. First check the **Kelola Stok BHP** (Manage Consumables Stock) menu to see the current list of consumables and their stock.
  2. Go to the **Log Maintenance** (Maintenance Log) menu.
  3. Register a new maintenance entry if an active asset is broken or needs maintenance. Select the problematic asset, and write the maintenance action details.
  4. On the maintenance form, select the type and quantity of **BHP (Bahan Habis Pakai / Consumables)** used for the repair process (e.g. thermal paste, rubbing alcohol, etc.).
  5. Save the maintenance log. Go back to the **Kelola Stok BHP** menu: the system will automatically deduct the consumables stock quantity based on the amount used in the maintenance log.

---

## 6. Team Information
- Febrian Timotius Sugiarto - 2472039 - Project Leader
- Miracle Steven Gerrald - 2472019 - Team Member
- Felicia Ivanna Widian - 2472030 - Team Member

---
