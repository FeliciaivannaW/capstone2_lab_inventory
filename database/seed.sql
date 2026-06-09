-- ============================================================
-- BASE DATA (Roles, Users, Buildings, Floors, Room Types, Rooms, Labs)
-- Ditambahkan agar seed.sql bisa berdiri sendiri dan tidak error
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

-- Kosongkan dulu untuk mencegah duplikasi jika dijalankan ulang
TRUNCATE TABLE `roles`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `buildings`;
TRUNCATE TABLE `floors`;
TRUNCATE TABLE `room_types`;
TRUNCATE TABLE `rooms`;
TRUNCATE TABLE `laboratories`;
TRUNCATE TABLE `lab_group_laboratories`;
TRUNCATE TABLE `lab_group_users`;
TRUNCATE TABLE `lab_group_rooms`;
TRUNCATE TABLE `lab_groups`;
TRUNCATE TABLE `asset_condition_logs`;
TRUNCATE TABLE `bhp_stock_movements`;
TRUNCATE TABLE `maintenance_logs`;
TRUNCATE TABLE `inventory_assets`;
TRUNCATE TABLE `bhp_stocks`;
TRUNCATE TABLE `item_catalogs`;
TRUNCATE TABLE `item_categories`;

-- 1. Roles
INSERT INTO `roles` (id, name) VALUES 
(1, 'administrator'),
(2, 'kepala_laboratorium'),
(3, 'ketua_program_studi'),
(4, 'staf_administrasi'),
(5, 'staf_laboratorium');

-- 2. Users
INSERT INTO `users` (id, role_id, name, email, password, status) VALUES
(1, 1, 'Administrator', 'admin@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active'),
(2, 2, 'Kepala Laboratorium', 'kalab@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active'),
(3, 3, 'Ketua Program Studi', 'kaprodi@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active'),
(4, 4, 'Staf Administrasi', 'stafadmin@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active'),
(5, 5, 'Staf Lab 1', 'staflab@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active'),
(6, 5, 'Staf Lab Multi', 'staflab.multi@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active'),
(7, 5, 'Staf Lab Jaringan', 'staflab.jaringan@example.com', '$2b$10$I./CizOyzoKxdJwRNztJkuK3JtOcWOTaezTWq2hF5wbCxpc9ZT9q.', 'active');

-- 3. Buildings & Floors & Room Types
INSERT INTO `buildings` (id, name, code) VALUES (1, 'Gedung H', 'H'), (2, 'Gedung FTI', 'FTI');
INSERT INTO `floors` (id, building_id, floor_number, name) VALUES (1, 1, 8, 'Lantai 8'), (2, 2, 2, 'Lantai 2');
INSERT INTO `room_types` (id, name) VALUES (1, 'Laboratorium');

-- 4. Rooms
INSERT INTO `rooms` (id, floor_id, room_type_id, code, name) VALUES
(1, 1, 1, 'H08-A02', 'Ruang H08-A02'),
(2, 1, 1, 'H08-A03', 'Ruang H08-A03'),
(3, 1, 1, 'H08-A04', 'Ruang H08-A04'),
(4, 1, 1, 'H08-B02', 'Ruang H08-B02'),
(5, 1, 1, 'H08-B03', 'Ruang H08-B03'),
(6, 1, 1, 'H08-B06', 'Ruang H08-B06'),
(7, 1, 1, 'H08-B08', 'Ruang H08-B08'),
(8, 1, 1, 'H08-B09', 'Ruang H08-B09'),
(9, 1, 1, 'H08-B10', 'Ruang H08-B10'),
(10, 1, 1, 'H08-B11', 'Ruang H08-B11'),
(11, 1, 1, 'H08-C03', 'Ruang H08-C03'),
(12, 1, 1, 'H08-C04', 'Ruang H08-C04'),
(13, 2, 1, 'FTI-201', 'Ruang FTI-201');

-- 5. Laboratories
INSERT INTO `laboratories` (id, room_id, head_user_id, name, code) VALUES
(1, (SELECT id FROM rooms WHERE code = 'H08-A03'), 2, 'Lab Programming 1', 'LAB-PROG-1'),
(2, (SELECT id FROM rooms WHERE code = 'H08-A04'), 2, 'Lab Programming 2', 'LAB-PROG-2'),
(3, (SELECT id FROM rooms WHERE code = 'H08-B02'), 2, 'Lab Adv Programming 1', 'LAB-ADVPROG-1'),
(4, (SELECT id FROM rooms WHERE code = 'H08-B03'), 2, 'Lab Adv Programming 2', 'LAB-ADVPROG-2'),
(5, (SELECT id FROM rooms WHERE code = 'H08-B08'), 2, 'Lab Adv Programming 3', 'LAB-ADVPROG-3'),
(6, (SELECT id FROM rooms WHERE code = 'H08-B09'), 2, 'Lab Adv Programming 4', 'LAB-ADVPROG-4'),
(7, (SELECT id FROM rooms WHERE code = 'H08-A02'), 2, 'Lab Computer Network', 'LAB-COMNET'),
(8, (SELECT id FROM rooms WHERE code = 'H08-B10'), 2, 'Lab Internet 1', 'LAB-INET-1'),
(9, (SELECT id FROM rooms WHERE code = 'H08-B11'), 2, 'Lab Internet 2', 'LAB-INET-2'),
(10, (SELECT id FROM rooms WHERE code = 'H08-C03'), 2, 'Lab Database', 'LAB-DB'),
(11, (SELECT id FROM rooms WHERE code = 'FTI-201'), 2, 'Lab AI', 'LAB-AI'),
(12, (SELECT id FROM rooms WHERE code = 'H08-C04'), 2, 'Lab Multimedia', 'LAB-MM');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- LAB GROUP ACCESS
-- Dipakai supaya admin bisa melihat grup staf lab mengurus lab apa saja.
-- Satu grup bisa mengelola lebih dari satu laboratorium.
-- ============================================================

INSERT INTO lab_groups (laboratory_id, name, description) VALUES
((SELECT id FROM laboratories WHERE code = 'LAB-PROG-1'), 'Grup Staff Programming', 'Grup staf lab untuk Programming dan Advance Programming Lab'),
((SELECT id FROM laboratories WHERE code = 'LAB-COMNET'), 'Grup Staff Jaringan', 'Grup staf lab untuk Computer Network dan Internet Lab'),
((SELECT id FROM laboratories WHERE code = 'LAB-DB'), 'Grup Staff Database', 'Grup staf lab untuk Database dan AI Lab'),
((SELECT id FROM laboratories WHERE code = 'LAB-MM'), 'Grup Staff Multimedia', 'Grup staf lab untuk Multimedia Lab');

INSERT INTO lab_group_laboratories (group_id, laboratory_id)
SELECT g.id, l.id
FROM lab_groups g
JOIN laboratories l ON l.code IN (
  'LAB-PROG-1',
  'LAB-PROG-2',
  'LAB-ADVPROG-1',
  'LAB-ADVPROG-2',
  'LAB-ADVPROG-3',
  'LAB-ADVPROG-4'
)
WHERE g.name = 'Grup Staff Programming';

INSERT INTO lab_group_laboratories (group_id, laboratory_id)
SELECT g.id, l.id
FROM lab_groups g
JOIN laboratories l ON l.code IN (
  'LAB-COMNET',
  'LAB-INET-1',
  'LAB-INET-2'
)
WHERE g.name = 'Grup Staff Jaringan';

INSERT INTO lab_group_laboratories (group_id, laboratory_id)
SELECT g.id, l.id
FROM lab_groups g
JOIN laboratories l ON l.code IN (
  'LAB-DB',
  'LAB-AI'
)
WHERE g.name = 'Grup Staff Database';

INSERT INTO lab_group_laboratories (group_id, laboratory_id)
SELECT g.id, l.id
FROM lab_groups g
JOIN laboratories l ON l.code IN (
  'LAB-MM'
)
WHERE g.name = 'Grup Staff Multimedia';

INSERT INTO lab_group_users (group_id, user_id, role_in_group) VALUES
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM users WHERE email = 'staflab@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM users WHERE email = 'staflab.multi@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Jaringan'), (SELECT id FROM users WHERE email = 'staflab.jaringan@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Jaringan'), (SELECT id FROM users WHERE email = 'staflab.multi@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Database'), (SELECT id FROM users WHERE email = 'staflab.multi@example.com'), 'staf_lab');

INSERT INTO lab_group_rooms (group_id, room_id)
SELECT g.id, r.id
FROM lab_groups g
JOIN rooms r ON r.code IN (
  'H08-A03',
  'H08-A04',
  'H08-B02',
  'H08-B03',
  'H08-B08',
  'H08-B09'
)
WHERE g.name = 'Grup Staff Programming';

INSERT INTO lab_group_rooms (group_id, room_id)
SELECT g.id, r.id
FROM lab_groups g
JOIN rooms r ON r.code IN (
  'H08-A02',
  'H08-B10',
  'H08-B11'
)
WHERE g.name = 'Grup Staff Jaringan';

INSERT INTO lab_group_rooms (group_id, room_id)
SELECT g.id, r.id
FROM lab_groups g
JOIN rooms r ON r.code IN (
  'H08-C03',
  'FTI-201'
)
WHERE g.name = 'Grup Staff Database';

INSERT INTO lab_group_rooms (group_id, room_id)
SELECT g.id, r.id
FROM lab_groups g
JOIN rooms r ON r.code IN (
  'H08-C04'
)
WHERE g.name = 'Grup Staff Multimedia';

INSERT INTO item_categories (name, description) VALUES
('Komputer dan Perangkat', 'Barang inventaris seperti PC, monitor, keyboard, dan mouse'),
('Jaringan', 'Perangkat jaringan seperti router, switch, access point, dan kabel LAN'),
('Bahan Habis Pakai', 'Barang habis pakai untuk kebutuhan laboratorium'),
('Furniture', 'Meja, kursi, lemari, dan perlengkapan ruangan');

INSERT INTO item_catalogs (category_id, name, type, unit, description) VALUES
((SELECT id FROM item_categories WHERE name = 'Komputer dan Perangkat'), 'PC Desktop', 'inventory', 'unit', 'Komputer desktop untuk praktikum'),
((SELECT id FROM item_categories WHERE name = 'Komputer dan Perangkat'), 'Monitor', 'inventory', 'unit', 'Monitor komputer'),
((SELECT id FROM item_categories WHERE name = 'Komputer dan Perangkat'), 'Keyboard', 'inventory', 'unit', 'Keyboard komputer'),
((SELECT id FROM item_categories WHERE name = 'Komputer dan Perangkat'), 'Mouse', 'inventory', 'unit', 'Mouse komputer'),
((SELECT id FROM item_categories WHERE name = 'Jaringan'), 'Router', 'inventory', 'unit', 'Perangkat router jaringan'),
((SELECT id FROM item_categories WHERE name = 'Jaringan'), 'Switch', 'inventory', 'unit', 'Perangkat switch jaringan'),
((SELECT id FROM item_categories WHERE name = 'Jaringan'), 'Access Point', 'inventory', 'unit', 'Perangkat access point'),
((SELECT id FROM item_categories WHERE name = 'Jaringan'), 'Kabel LAN', 'bhp', 'meter', 'Kabel LAN untuk kebutuhan jaringan'),
((SELECT id FROM item_categories WHERE name = 'Bahan Habis Pakai'), 'Thermal Paste', 'bhp', 'pcs', 'Thermal paste untuk maintenance komputer'),
((SELECT id FROM item_categories WHERE name = 'Bahan Habis Pakai'), 'Cable Tie', 'bhp', 'pcs', 'Cable tie untuk pengaturan kabel'),
((SELECT id FROM item_categories WHERE name = 'Bahan Habis Pakai'), 'Tisu Pembersih Elektronik', 'bhp', 'pack', 'Tisu untuk membersihkan perangkat lab'),
((SELECT id FROM item_categories WHERE name = 'Furniture'), 'Meja Komputer', 'inventory', 'unit', 'Meja komputer laboratorium'),
((SELECT id FROM item_categories WHERE name = 'Furniture'), 'Kursi Laboratorium', 'inventory', 'unit', 'Kursi laboratorium');

INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit)
SELECT
    laboratories.id,
    item_catalogs.id,
    CASE
        WHEN item_catalogs.name = 'Kabel LAN' THEN 100
        WHEN item_catalogs.name = 'Thermal Paste' THEN 10
        WHEN item_catalogs.name = 'Cable Tie' THEN 200
        WHEN item_catalogs.name = 'Tisu Pembersih Elektronik' THEN 25
        ELSE 0
    END AS current_stock,
    CASE
        WHEN item_catalogs.name = 'Kabel LAN' THEN 20
        WHEN item_catalogs.name = 'Thermal Paste' THEN 3
        WHEN item_catalogs.name = 'Cable Tie' THEN 50
        WHEN item_catalogs.name = 'Tisu Pembersih Elektronik' THEN 5
        ELSE 0
    END AS minimum_stock,
    item_catalogs.unit
FROM laboratories
JOIN item_catalogs
WHERE item_catalogs.type = 'bhp';

INSERT INTO inventory_assets (item_catalog_id, room_id, asset_code, label_number, serial_number, purchase_price, purchase_date, received_date, asset_condition, status, photo_url, notes) VALUES
((SELECT id FROM item_catalogs WHERE name = 'PC Desktop'), (SELECT id FROM rooms WHERE code = 'H08-A03'), 'INV-LAB-PROG-1-2025-001', 'LBL-LAB-PROG-1-001', 'PCP1001', 8500000, '2025-01-15', '2025-01-20', 'baik', 'available', NULL, 'PC untuk praktikum programming'),
((SELECT id FROM item_catalogs WHERE name = 'PC Desktop'), (SELECT id FROM rooms WHERE code = 'H08-A03'), 'INV-LAB-PROG-1-2025-002', 'LBL-LAB-PROG-1-002', 'PCP1002', 8500000, '2025-01-15', '2025-01-20', 'maintenance', 'maintenance', NULL, 'PC sedang dicek karena lambat'),
((SELECT id FROM item_catalogs WHERE name = 'Monitor'), (SELECT id FROM rooms WHERE code = 'H08-A03'), 'INV-LAB-PROG-1-2025-003', 'LBL-LAB-PROG-1-003', 'MON1001', 1700000, '2025-01-15', '2025-01-20', 'baik', 'available', NULL, 'Monitor untuk PC programming'),
((SELECT id FROM item_catalogs WHERE name = 'Router'), (SELECT id FROM rooms WHERE code = 'H08-A02'), 'INV-LAB-COMNET-2025-001', 'LBL-LAB-COMNET-001', 'RTR1001', 2500000, '2025-02-10', '2025-02-15', 'baik', 'available', NULL, 'Router praktikum jaringan'),
((SELECT id FROM item_catalogs WHERE name = 'Switch'), (SELECT id FROM rooms WHERE code = 'H08-A02'), 'INV-LAB-COMNET-2025-002', 'LBL-LAB-COMNET-002', 'SW1001', 3200000, '2025-02-10', '2025-02-15', 'rusak_ringan', 'available', NULL, 'Switch perlu pengecekan port'),
((SELECT id FROM item_catalogs WHERE name = 'Access Point'), (SELECT id FROM rooms WHERE code = 'H08-B06'), 'INV-H08-B06-2025-001', 'LBL-H08-B06-001', 'AP1001', 1200000, '2025-03-05', '2025-03-09', 'baik', 'available', NULL, 'Access point cadangan server room');
INSERT INTO inventory_assets (
    item_catalog_id,
    room_id,
    asset_code,
    label_number,
    serial_number,
    purchase_price,
    purchase_date,
    received_date,
    asset_condition,
    status,
    photo_url,
    notes
) VALUES
((SELECT id FROM item_catalogs WHERE name = 'Keyboard'), (SELECT id FROM rooms WHERE code = 'H08-A04'), 'INV-LAB-PROG-2-2025-001', 'LBL-LAB-PROG-2-001', 'KBP2001', 250000, '2025-02-01', '2025-02-08', 'baik', 'available', NULL, 'Keyboard untuk Programming Lab 2'),
((SELECT id FROM item_catalogs WHERE name = 'Mouse'), (SELECT id FROM rooms WHERE code = 'H08-A04'), 'INV-LAB-PROG-2-2025-002', 'LBL-LAB-PROG-2-002', 'MSP2001', 150000, '2025-02-01', '2025-02-08', 'baik', 'available', NULL, 'Mouse untuk Programming Lab 2'),
((SELECT id FROM item_catalogs WHERE name = 'Monitor'), (SELECT id FROM rooms WHERE code = 'H08-A04'), 'INV-LAB-PROG-2-2025-003', NULL, 'MON2001', 1750000, '2025-02-01', '2025-02-08', 'baik', 'received', NULL, 'Monitor baru belum diberi label'),
((SELECT id FROM item_catalogs WHERE name = 'Meja Komputer'), (SELECT id FROM rooms WHERE code = 'H08-C03'), 'INV-LAB-DB-2025-001', 'LBL-LAB-DB-001', 'DSK3001', 1200000, '2025-01-12', '2025-01-18', 'baik', 'available', NULL, 'Meja komputer untuk Database Lab'),
((SELECT id FROM item_catalogs WHERE name = 'Kursi Laboratorium'), (SELECT id FROM rooms WHERE code = 'H08-C04'), 'INV-LAB-MM-2025-001', NULL, 'CHR4001', 450000, '2025-03-01', '2025-03-06', 'baik', 'labeled', NULL, 'Kursi laboratorium multimedia'),
((SELECT id FROM item_catalogs WHERE name = 'PC Desktop'), (SELECT id FROM rooms WHERE code = 'FTI-201'), 'INV-LAB-AI-2025-001', 'LBL-LAB-AI-001', 'PCAI001', 9200000, '2025-03-15', '2025-03-21', 'baik', 'available', NULL, 'PC untuk AI Lab'),
((SELECT id FROM item_catalogs WHERE name = 'Monitor'), (SELECT id FROM rooms WHERE code = 'FTI-201'), 'INV-LAB-AI-2025-002', 'LBL-LAB-AI-002', 'MONAI001', 1800000, '2025-03-15', '2025-03-21', 'rusak_ringan', 'maintenance', NULL, 'Monitor AI Lab sedang dicek');

INSERT INTO maintenance_logs (inventory_asset_id, performed_by, maintenance_date, issue_description, action_taken, condition_before, condition_after, status, cost, notes) VALUES
((SELECT id FROM inventory_assets WHERE asset_code = 'INV-LAB-PROG-1-2025-002'), (SELECT id FROM users WHERE email = 'staflab@example.com'), '2025-05-20', 'PC lambat dan suhu cepat panas', 'Membersihkan debu dan mengganti thermal paste', 'maintenance', 'baik', 'done', 50000, 'Maintenance selesai dan stok thermal paste berkurang'),
((SELECT id FROM inventory_assets WHERE asset_code = 'INV-LAB-COMNET-2025-002'), (SELECT id FROM users WHERE email = 'staflab.jaringan@example.com'), '2025-05-22', 'Beberapa port switch tidak stabil', 'Membersihkan port dan merapikan kabel LAN', 'rusak_ringan', 'baik', 'done', 25000, 'Maintenance selesai dan kabel LAN terpakai');

INSERT INTO asset_condition_logs (inventory_asset_id, updated_by, old_condition, new_condition, note) VALUES
((SELECT id FROM inventory_assets WHERE asset_code = 'INV-LAB-PROG-1-2025-002'), (SELECT id FROM users WHERE email = 'staflab@example.com'), 'maintenance', 'baik', 'Kondisi membaik setelah maintenance'),
((SELECT id FROM inventory_assets WHERE asset_code = 'INV-LAB-COMNET-2025-002'), (SELECT id FROM users WHERE email = 'staflab.jaringan@example.com'), 'rusak_ringan', 'baik', 'Port switch sudah dicek');

INSERT INTO bhp_stock_movements (stock_id, maintenance_id, performed_by, movement_type, quantity, movement_date, note) VALUES
((SELECT bs.id FROM bhp_stocks bs JOIN laboratories l ON bs.lab_id = l.id JOIN item_catalogs ic ON bs.item_catalog_id = ic.id WHERE l.code = 'LAB-PROG-1' AND ic.name = 'Thermal Paste'), (SELECT id FROM maintenance_logs WHERE notes = 'Maintenance selesai dan stok thermal paste berkurang'), (SELECT id FROM users WHERE email = 'staflab@example.com'), 'maintenance_usage', 1, '2025-05-20 10:30:00', 'Thermal paste digunakan saat maintenance PC'),
((SELECT bs.id FROM bhp_stocks bs JOIN laboratories l ON bs.lab_id = l.id JOIN item_catalogs ic ON bs.item_catalog_id = ic.id WHERE l.code = 'LAB-COMNET' AND ic.name = 'Kabel LAN'), (SELECT id FROM maintenance_logs WHERE notes = 'Maintenance selesai dan kabel LAN terpakai'), (SELECT id FROM users WHERE email = 'staflab.jaringan@example.com'), 'maintenance_usage', 5, '2025-05-22 13:00:00', 'Kabel LAN digunakan saat maintenance switch');

UPDATE bhp_stocks bs
JOIN laboratories l ON bs.lab_id = l.id
JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
SET bs.current_stock = bs.current_stock - 1
WHERE l.code = 'LAB-PROG-1' AND ic.name = 'Thermal Paste';

UPDATE bhp_stocks bs
JOIN laboratories l ON bs.lab_id = l.id
JOIN item_catalogs ic ON bs.item_catalog_id = ic.id
SET bs.current_stock = bs.current_stock - 5
WHERE l.code = 'LAB-COMNET' AND ic.name = 'Kabel LAN';
