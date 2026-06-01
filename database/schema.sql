CREATE DATABASE IF NOT EXISTS lab_inventory_db;
USE lab_inventory_db;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS bhp_stock_movements;
DROP TABLE IF EXISTS lab_group_users;
DROP TABLE IF EXISTS lab_group_rooms;
DROP TABLE IF EXISTS lab_groups;
DROP TABLE IF EXISTS bhp_stocks;
DROP TABLE IF EXISTS maintenance_logs;
DROP TABLE IF EXISTS asset_disposals;
DROP TABLE IF EXISTS asset_condition_logs;
DROP TABLE IF EXISTS inventory_assets;
DROP TABLE IF EXISTS goods_receipts;
DROP TABLE IF EXISTS procurement_items;
DROP TABLE IF EXISTS procurement_drafts;
DROP TABLE IF EXISTS item_catalogs;
DROP TABLE IF EXISTS item_categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS laboratories;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS room_types;
DROP TABLE IF EXISTS floors;
DROP TABLE IF EXISTS buildings;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    address VARCHAR(255),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    floor_number INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_floors_building
        FOREIGN KEY (building_id) REFERENCES buildings(id),

    CONSTRAINT unique_floor_per_building
        UNIQUE (building_id, floor_number)
);

CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    room_type_id INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    capacity INT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rooms_floor
        FOREIGN KEY (floor_id) REFERENCES floors(id),

    CONSTRAINT fk_rooms_room_type
        FOREIGN KEY (room_type_id) REFERENCES room_types(id)
);

CREATE TABLE laboratories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL UNIQUE,
    head_user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_laboratories_room
        FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    lab_id INT NULL,
    name VARCHAR(150) NOT NULL,
    nrp_nip VARCHAR(50),
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id),

    CONSTRAINT fk_users_lab
        FOREIGN KEY (lab_id) REFERENCES laboratories(id)
);

ALTER TABLE laboratories
ADD CONSTRAINT fk_laboratories_head_user
FOREIGN KEY (head_user_id) REFERENCES users(id);


CREATE TABLE lab_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laboratory_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_lab_groups_laboratory
        FOREIGN KEY (laboratory_id) REFERENCES laboratories(id),

    CONSTRAINT unique_lab_group_name_per_lab
        UNIQUE (laboratory_id, name)
);

CREATE TABLE lab_group_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role_in_group ENUM('kepala_lab', 'staf_lab') NOT NULL DEFAULT 'staf_lab',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lab_group_users_group
        FOREIGN KEY (group_id) REFERENCES lab_groups(id),

    CONSTRAINT fk_lab_group_users_user
        FOREIGN KEY (user_id) REFERENCES users(id),

    CONSTRAINT unique_lab_group_user
        UNIQUE (group_id, user_id)
);

CREATE TABLE lab_group_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    room_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lab_group_rooms_group
        FOREIGN KEY (group_id) REFERENCES lab_groups(id),

    CONSTRAINT fk_lab_group_rooms_room
        FOREIGN KEY (room_id) REFERENCES rooms(id),

    CONSTRAINT unique_lab_group_room
        UNIQUE (group_id, room_id)
);

CREATE TABLE item_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE item_catalogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM('inventory', 'bhp') NOT NULL,
    unit VARCHAR(50) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_item_catalogs_category
        FOREIGN KEY (category_id) REFERENCES item_categories(id)
);

CREATE TABLE procurement_drafts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_id INT NOT NULL,
    created_by INT NOT NULL,
    finalized_by INT NULL,
    title VARCHAR(200) NOT NULL,
    budget_year YEAR NOT NULL,
    status ENUM('draft', 'submitted', 'finalized', 'rejected') NOT NULL DEFAULT 'draft',
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT,
    submitted_at DATETIME NULL,
    finalized_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_procurement_drafts_lab
        FOREIGN KEY (lab_id) REFERENCES laboratories(id),

    CONSTRAINT fk_procurement_drafts_created_by
        FOREIGN KEY (created_by) REFERENCES users(id),

    CONSTRAINT fk_procurement_drafts_finalized_by
        FOREIGN KEY (finalized_by) REFERENCES users(id)
);

CREATE TABLE procurement_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    draft_id INT NOT NULL,
    item_catalog_id INT NULL,
    replacement_asset_id INT NULL,
    reviewed_by INT NULL,
    item_name VARCHAR(150) NOT NULL,
    item_description TEXT,
    item_type ENUM('inventory', 'bhp') NOT NULL,
    quantity INT NOT NULL,
    estimated_price DECIMAL(12,2) NOT NULL,
    purchase_link VARCHAR(255),
    review_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    review_note TEXT,
    reviewed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_procurement_items_draft
        FOREIGN KEY (draft_id) REFERENCES procurement_drafts(id),

    CONSTRAINT fk_procurement_items_catalog
        FOREIGN KEY (item_catalog_id) REFERENCES item_catalogs(id),

    CONSTRAINT fk_procurement_items_reviewed_by
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

CREATE TABLE goods_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_item_id INT NOT NULL,
    received_by INT NOT NULL,
    received_date DATE NOT NULL,
    quantity_received INT NOT NULL,
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_goods_receipts_procurement_item
        FOREIGN KEY (procurement_item_id) REFERENCES procurement_items(id),

    CONSTRAINT fk_goods_receipts_received_by
        FOREIGN KEY (received_by) REFERENCES users(id)
);

CREATE TABLE inventory_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_catalog_id INT NOT NULL,
    procurement_item_id INT NULL,
    receipt_id INT NULL,
    room_id INT NULL,
    replaced_by_asset_id INT NULL,
    asset_code VARCHAR(100) NOT NULL UNIQUE,
    label_number VARCHAR(100),
    qr_code VARCHAR(255),
    barcode VARCHAR(255),
    serial_number VARCHAR(100),
    purchase_price DECIMAL(12,2),
    purchase_date DATE,
    received_date DATE,
    asset_condition ENUM('baik', 'rusak_ringan', 'rusak_berat', 'maintenance', 'dihapus', 'diganti') NOT NULL DEFAULT 'baik',
    status ENUM('received', 'labeled', 'available', 'in_use', 'maintenance', 'disposed', 'replaced') NOT NULL DEFAULT 'received',
    photo_url VARCHAR(255),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_inventory_assets_catalog
        FOREIGN KEY (item_catalog_id) REFERENCES item_catalogs(id),

    CONSTRAINT fk_inventory_assets_procurement_item
        FOREIGN KEY (procurement_item_id) REFERENCES procurement_items(id),

    CONSTRAINT fk_inventory_assets_receipt
        FOREIGN KEY (receipt_id) REFERENCES goods_receipts(id),

    CONSTRAINT fk_inventory_assets_room
        FOREIGN KEY (room_id) REFERENCES rooms(id)
);

ALTER TABLE inventory_assets
ADD CONSTRAINT fk_inventory_assets_replaced_by
FOREIGN KEY (replaced_by_asset_id) REFERENCES inventory_assets(id);

ALTER TABLE procurement_items
ADD CONSTRAINT fk_procurement_items_replacement_asset
FOREIGN KEY (replacement_asset_id) REFERENCES inventory_assets(id);

CREATE TABLE asset_condition_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_asset_id INT NOT NULL,
    updated_by INT NOT NULL,
    old_condition ENUM('baik', 'rusak_ringan', 'rusak_berat', 'maintenance', 'dihapus', 'diganti') NULL,
    new_condition ENUM('baik', 'rusak_ringan', 'rusak_berat', 'maintenance', 'dihapus', 'diganti') NOT NULL,
    note TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_asset_condition_logs_asset
        FOREIGN KEY (inventory_asset_id) REFERENCES inventory_assets(id),

    CONSTRAINT fk_asset_condition_logs_updated_by
        FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE asset_disposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_asset_id INT NOT NULL,
    disposed_by INT NOT NULL,
    disposal_date DATE NOT NULL,
    reason TEXT NOT NULL,
    disposal_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_asset_disposals_asset
        FOREIGN KEY (inventory_asset_id) REFERENCES inventory_assets(id),

    CONSTRAINT fk_asset_disposals_disposed_by
        FOREIGN KEY (disposed_by) REFERENCES users(id)
);

CREATE TABLE maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_asset_id INT NOT NULL,
    performed_by INT NOT NULL,
    maintenance_date DATE NOT NULL,
    issue_description TEXT,
    action_taken TEXT,
    condition_before ENUM('baik', 'rusak_ringan', 'rusak_berat', 'maintenance', 'dihapus', 'diganti') NULL,
    condition_after ENUM('baik', 'rusak_ringan', 'rusak_berat', 'maintenance', 'dihapus', 'diganti') NULL,
    status ENUM('planned', 'in_progress', 'done', 'cancelled') NOT NULL DEFAULT 'planned',
    cost DECIMAL(12,2),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_maintenance_logs_asset
        FOREIGN KEY (inventory_asset_id) REFERENCES inventory_assets(id),

    CONSTRAINT fk_maintenance_logs_performed_by
        FOREIGN KEY (performed_by) REFERENCES users(id)
);

CREATE TABLE bhp_stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_id INT NOT NULL,
    item_catalog_id INT NOT NULL,
    current_stock INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 0,
    unit VARCHAR(50) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_bhp_stocks_lab
        FOREIGN KEY (lab_id) REFERENCES laboratories(id),

    CONSTRAINT fk_bhp_stocks_catalog
        FOREIGN KEY (item_catalog_id) REFERENCES item_catalogs(id),

    CONSTRAINT unique_bhp_stock_per_lab_item
        UNIQUE (lab_id, item_catalog_id)
);

CREATE TABLE bhp_stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT NOT NULL,
    procurement_item_id INT NULL,
    receipt_id INT NULL,
    maintenance_id INT NULL,
    performed_by INT NOT NULL,
    movement_type ENUM('in', 'out', 'adjustment', 'maintenance_usage') NOT NULL,
    quantity INT NOT NULL,
    movement_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_bhp_stock_movements_stock
        FOREIGN KEY (stock_id) REFERENCES bhp_stocks(id),

    CONSTRAINT fk_bhp_stock_movements_procurement_item
        FOREIGN KEY (procurement_item_id) REFERENCES procurement_items(id),

    CONSTRAINT fk_bhp_stock_movements_receipt
        FOREIGN KEY (receipt_id) REFERENCES goods_receipts(id),

    CONSTRAINT fk_bhp_stock_movements_maintenance
        FOREIGN KEY (maintenance_id) REFERENCES maintenance_logs(id),

    CONSTRAINT fk_bhp_stock_movements_performed_by
        FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Laravel Framework Tables
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_last_activity (last_activity)
);

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    INDEX idx_cache_expiration (expiration)
);

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    INDEX idx_cache_locks_expiration (expiration)
);