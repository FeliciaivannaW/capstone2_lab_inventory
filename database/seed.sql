USE lab_inventory_db;

INSERT INTO roles (name) VALUES
('administrator'),
('kepala_laboratorium'),
('ketua_program_studi'),
('staf_administrasi'),
('staf_laboratorium');

INSERT INTO buildings (name, code, address, description) VALUES
('Gedung GWM', 'GWM', NULL, 'Gedung GWM');

INSERT INTO floors (building_id, floor_number, name, description) VALUES
(1, 8, 'Lantai 8', 'Denah Gedung GWM lantai 8');

INSERT INTO room_types (name, description) VALUES
('laboratory', 'Ruangan laboratorium'),
('office', 'Ruangan kantor atau administrasi'),
('storage', 'Ruangan penyimpanan'),
('meeting_room', 'Ruangan rapat'),
('study_room', 'Ruangan belajar'),
('server_room', 'Ruangan server'),
('toilet', 'Toilet'),
('utility', 'Ruangan utilitas seperti panel listrik atau janitor'),
('waiting_room', 'Ruang tunggu');

INSERT INTO rooms (floor_id, room_type_id, code, name, capacity, description) VALUES
(1, 5, 'H08-A01', 'Study Room', NULL, 'Study room pada denah GWM lantai 8'),
(1, 1, 'H08-A02', 'Computer Network Lab', NULL, 'Laboratorium jaringan komputer'),
(1, 1, 'H08-A03', 'Programming Lab 1', NULL, 'Laboratorium programming 1'),
(1, 1, 'H08-A04', 'Programming Lab 2', NULL, 'Laboratorium programming 2'),
(1, 3, 'H08-A05', 'Storage 1', NULL, 'Ruang penyimpanan 1'),
(1, 2, 'H08-A06', 'Master Program of Computer Science', NULL, 'Ruang program magister ilmu komputer'),
(1, 1, 'H08-A07', 'Enterprise Lab 1', NULL, 'Laboratorium enterprise 1'),
(1, 1, 'H08-A08', 'Enterprise Lab 2', NULL, 'Laboratorium enterprise 2'),
(1, 8, 'H08-A09', 'Janitor Room 1', NULL, 'Ruang janitor 1'),
(1, 8, 'H08-A10', 'Electricity Panel Room 1', NULL, 'Ruang panel listrik 1'),
(1, 7, 'H08-A11', 'Gents Toilet', NULL, 'Toilet pria area A'),
(1, 7, 'H08-A12', 'Ladies Toilet', NULL, 'Toilet wanita area A'),

(1, 2, 'H08-B01', 'Staff Room 1', NULL, 'Ruang staff 1'),
(1, 1, 'H08-B02', 'Advance Programming Lab 1', NULL, 'Laboratorium advance programming 1'),
(1, 1, 'H08-B03', 'Advance Programming Lab 2', NULL, 'Laboratorium advance programming 2'),
(1, 2, 'H08-B04', 'Administration Room', NULL, 'Ruang administrasi'),
(1, 2, 'H08-B05', 'Head of Laboratory Office', NULL, 'Ruang kepala laboratorium'),
(1, 6, 'H08-B06', 'Server Room', NULL, 'Ruang server'),
(1, 3, 'H08-B07', 'Storage 2', NULL, 'Ruang penyimpanan 2'),
(1, 1, 'H08-B08', 'Advance Programming Lab 3', NULL, 'Laboratorium advance programming 3'),
(1, 1, 'H08-B09', 'Advance Programming Lab 4', NULL, 'Laboratorium advance programming 4'),
(1, 1, 'H08-B10', 'Internet Lab 1', NULL, 'Laboratorium internet 1'),
(1, 1, 'H08-B11', 'Internet Lab 2', NULL, 'Laboratorium internet 2'),

(1, 2, 'H08-C01', 'Staff Room 2', NULL, 'Ruang staff 2'),
(1, 9, 'H08-C02', 'Waiting Room', NULL, 'Ruang tunggu'),
(1, 1, 'H08-C03', 'Database Lab', NULL, 'Laboratorium database'),
(1, 1, 'H08-C04', 'Multimedia Lab', NULL, 'Laboratorium multimedia'),
(1, 4, 'H08-C05', 'Meeting Room', NULL, 'Ruang rapat'),
(1, 8, 'H08-C06', 'Electricity Panel Room 2', NULL, 'Ruang panel listrik 2'),
(1, 3, 'H08-C07', 'Storage 3', NULL, 'Ruang penyimpanan 3'),
(1, 7, 'H08-C08', 'Ladies Toilet', NULL, 'Toilet wanita area C'),
(1, 7, 'H08-C09', 'Gents Toilet', NULL, 'Toilet pria area C'),
(1, 8, 'H08-C10', 'Janitor Room 2', NULL, 'Ruang janitor 2');

INSERT INTO laboratories (room_id, name, code, description) VALUES
((SELECT id FROM rooms WHERE code = 'H08-A02'), 'Computer Network Lab', 'LAB-COMNET', 'Laboratorium jaringan komputer'),
((SELECT id FROM rooms WHERE code = 'H08-A03'), 'Programming Lab 1', 'LAB-PROG-1', 'Laboratorium programming 1'),
((SELECT id FROM rooms WHERE code = 'H08-A04'), 'Programming Lab 2', 'LAB-PROG-2', 'Laboratorium programming 2'),
((SELECT id FROM rooms WHERE code = 'H08-A07'), 'Enterprise Lab 1', 'LAB-ENT-1', 'Laboratorium enterprise 1'),
((SELECT id FROM rooms WHERE code = 'H08-A08'), 'Enterprise Lab 2', 'LAB-ENT-2', 'Laboratorium enterprise 2'),
((SELECT id FROM rooms WHERE code = 'H08-B02'), 'Advance Programming Lab 1', 'LAB-ADVPROG-1', 'Laboratorium advance programming 1'),
((SELECT id FROM rooms WHERE code = 'H08-B03'), 'Advance Programming Lab 2', 'LAB-ADVPROG-2', 'Laboratorium advance programming 2'),
((SELECT id FROM rooms WHERE code = 'H08-B08'), 'Advance Programming Lab 3', 'LAB-ADVPROG-3', 'Laboratorium advance programming 3'),
((SELECT id FROM rooms WHERE code = 'H08-B09'), 'Advance Programming Lab 4', 'LAB-ADVPROG-4', 'Laboratorium advance programming 4'),
((SELECT id FROM rooms WHERE code = 'H08-B10'), 'Internet Lab 1', 'LAB-INET-1', 'Laboratorium internet 1'),
((SELECT id FROM rooms WHERE code = 'H08-B11'), 'Internet Lab 2', 'LAB-INET-2', 'Laboratorium internet 2'),
((SELECT id FROM rooms WHERE code = 'H08-C03'), 'Database Lab', 'LAB-DB', 'Laboratorium database'),
((SELECT id FROM rooms WHERE code = 'H08-C04'), 'Multimedia Lab', 'LAB-MM', 'Laboratorium multimedia');

INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password, status) VALUES
((SELECT id FROM roles WHERE name = 'administrator'), NULL, 'Admin Sistem', 'ADM001', 'admin@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'kepala_laboratorium'), (SELECT id FROM laboratories WHERE code = 'LAB-PROG-1'), 'Kepala Laboratorium', 'KALAB001', 'kalab@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'ketua_program_studi'), NULL, 'Ketua Program Studi', 'KAPRODI001', 'kaprodi@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'staf_administrasi'), NULL, 'Staf Administrasi', 'STAFFADM001', 'stafadmin@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'staf_laboratorium'), (SELECT id FROM laboratories WHERE code = 'LAB-PROG-1'), 'Staf Laboratorium', 'STAFFLAB001', 'staflab@example.com', 'password123', 'active');

SET SQL_SAFE_UPDATES = 0;

UPDATE laboratories
SET head_user_id = (SELECT id FROM users WHERE email = 'kalab@example.com')
WHERE id > 0;

SET SQL_SAFE_UPDATES = 1;

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
        ELSE 0
    END AS current_stock,
    CASE 
        WHEN item_catalogs.name = 'Kabel LAN' THEN 20
        WHEN item_catalogs.name = 'Thermal Paste' THEN 3
        WHEN item_catalogs.name = 'Cable Tie' THEN 50
        ELSE 0
    END AS minimum_stock,
    item_catalogs.unit
FROM laboratories
JOIN item_catalogs
WHERE item_catalogs.type = 'bhp';