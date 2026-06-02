USE lab_inventory_db;

INSERT INTO roles (name) VALUES
('administrator'),
('kepala_laboratorium'),
('ketua_program_studi'),
('staf_administrasi'),
('staf_laboratorium');

INSERT INTO buildings (name, code, address, description) VALUES
('Gedung GWM', 'GWM', 'Jl. Surya Sumantri No. 65, Bandung', 'Gedung utama untuk ruang administrasi dan laboratorium'),
('Gedung FTI', 'FTI', 'Jl. Surya Sumantri No. 65, Bandung', 'Gedung fakultas untuk kebutuhan akademik dan praktikum');

INSERT INTO floors (building_id, floor_number, name, description) VALUES
((SELECT id FROM buildings WHERE code = 'GWM'), 7, 'Lantai 7', 'Area ruang kelas dan kantor'),
((SELECT id FROM buildings WHERE code = 'GWM'), 8, 'Lantai 8', 'Area laboratorium komputer dan ruang pendukung'),
((SELECT id FROM buildings WHERE code = 'FTI'), 2, 'Lantai 2', 'Area laboratorium tambahan dan ruang penyimpanan');

INSERT INTO room_types (name, description) VALUES
('laboratory', 'Ruangan laboratorium'),
('office', 'Ruangan kantor atau administrasi'),
('storage', 'Ruangan penyimpanan'),
('meeting_room', 'Ruangan rapat'),
('study_room', 'Ruangan belajar'),
('server_room', 'Ruangan server'),
('toilet', 'Toilet'),
('utility', 'Ruangan utilitas seperti panel listrik atau janitor'),
('waiting_room', 'Ruang tunggu'),
('classroom', 'Ruang kelas biasa');

INSERT INTO rooms (floor_id, room_type_id, code, name, capacity, description) VALUES
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'study_room'), 'H08-A01', 'Study Room', 24, 'Study room pada denah GWM lantai 8'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-A02', 'Computer Network Lab', 35, 'Laboratorium jaringan komputer'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-A03', 'Programming Lab 1', 40, 'Laboratorium programming 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-A04', 'Programming Lab 2', 40, 'Laboratorium programming 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'storage'), 'H08-A05', 'Storage 1', NULL, 'Ruang penyimpanan barang habis pakai'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'office'), 'H08-A06', 'Master Program of Computer Science', 20, 'Ruang program magister ilmu komputer'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-A07', 'Enterprise Lab 1', 35, 'Laboratorium enterprise 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-A08', 'Enterprise Lab 2', 35, 'Laboratorium enterprise 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'utility'), 'H08-A09', 'Janitor Room 1', NULL, 'Ruang janitor 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'utility'), 'H08-A10', 'Electricity Panel Room 1', NULL, 'Ruang panel listrik 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'toilet'), 'H08-A11', 'Gents Toilet', NULL, 'Toilet pria area A'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'toilet'), 'H08-A12', 'Ladies Toilet', NULL, 'Toilet wanita area A'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'office'), 'H08-B01', 'Staff Room 1', 20, 'Ruang staff 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-B02', 'Advance Programming Lab 1', 40, 'Laboratorium advance programming 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-B03', 'Advance Programming Lab 2', 40, 'Laboratorium advance programming 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'office'), 'H08-B04', 'Administration Room', 12, 'Ruang administrasi'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'office'), 'H08-B05', 'Head of Laboratory Office', 8, 'Ruang kepala laboratorium'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'server_room'), 'H08-B06', 'Server Room', NULL, 'Ruang server'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'storage'), 'H08-B07', 'Storage 2', NULL, 'Ruang penyimpanan 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-B08', 'Advance Programming Lab 3', 40, 'Laboratorium advance programming 3'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-B09', 'Advance Programming Lab 4', 40, 'Laboratorium advance programming 4'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-B10', 'Internet Lab 1', 35, 'Laboratorium internet 1'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-B11', 'Internet Lab 2', 35, 'Laboratorium internet 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'office'), 'H08-C01', 'Staff Room 2', 20, 'Ruang staff 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'waiting_room'), 'H08-C02', 'Waiting Room', 15, 'Ruang tunggu'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-C03', 'Database Lab', 40, 'Laboratorium database'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'laboratory'), 'H08-C04', 'Multimedia Lab', 35, 'Laboratorium multimedia'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'meeting_room'), 'H08-C05', 'Meeting Room', 20, 'Ruang rapat'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'utility'), 'H08-C06', 'Electricity Panel Room 2', NULL, 'Ruang panel listrik 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'storage'), 'H08-C07', 'Storage 3', NULL, 'Ruang penyimpanan 3'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'toilet'), 'H08-C08', 'Ladies Toilet', NULL, 'Toilet wanita area C'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'toilet'), 'H08-C09', 'Gents Toilet', NULL, 'Toilet pria area C'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 8), (SELECT id FROM room_types WHERE name = 'utility'), 'H08-C10', 'Janitor Room 2', NULL, 'Ruang janitor 2'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'GWM') AND floor_number = 7), (SELECT id FROM room_types WHERE name = 'classroom'), 'H07-D01', 'Ruang Kelas 701', 50, 'Ruang kelas untuk perkuliahan'),
((SELECT id FROM floors WHERE building_id = (SELECT id FROM buildings WHERE code = 'FTI') AND floor_number = 2), (SELECT id FROM room_types WHERE name = 'laboratory'), 'FTI-201', 'AI Lab', 30, 'Laboratorium AI tambahan');

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
((SELECT id FROM rooms WHERE code = 'H08-C04'), 'Multimedia Lab', 'LAB-MM', 'Laboratorium multimedia'),
((SELECT id FROM rooms WHERE code = 'FTI-201'), 'AI Lab', 'LAB-AI', 'Laboratorium AI tambahan');

INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password, status) VALUES
((SELECT id FROM roles WHERE name = 'administrator'), NULL, 'Admin Sistem', 'ADM001', 'admin@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'kepala_laboratorium'), (SELECT id FROM laboratories WHERE code = 'LAB-PROG-1'), 'Kepala Laboratorium', 'KALAB001', 'kalab@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'ketua_program_studi'), NULL, 'Ketua Program Studi', 'KAPRODI001', 'kaprodi@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'staf_administrasi'), NULL, 'Staf Administrasi', 'STAFFADM001', 'stafadmin@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'staf_laboratorium'), (SELECT id FROM laboratories WHERE code = 'LAB-PROG-1'), 'Staf Laboratorium Programming', 'STAFFLAB001', 'staflab@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'staf_laboratorium'), NULL, 'Staf Laboratorium Multi Lab', 'STAFFLAB002', 'staflab.multi@example.com', 'password123', 'active'),
((SELECT id FROM roles WHERE name = 'staf_laboratorium'), (SELECT id FROM laboratories WHERE code = 'LAB-COMNET'), 'Staf Laboratorium Jaringan', 'STAFFLAB003', 'staflab.jaringan@example.com', 'password123', 'active');

UPDATE laboratories
SET head_user_id = (SELECT id FROM users WHERE email = 'kalab@example.com')
WHERE head_user_id IS NULL;

INSERT INTO lab_groups (laboratory_id, name, description) VALUES
((SELECT id FROM laboratories WHERE code = 'LAB-PROG-1'), 'Grup Staff Programming', 'Grup staf lab untuk Programming Lab 1 dan Programming Lab 2'),
((SELECT id FROM laboratories WHERE code = 'LAB-COMNET'), 'Grup Staff Jaringan', 'Grup staf lab untuk Computer Network Lab'),
((SELECT id FROM laboratories WHERE code = 'LAB-DB'), 'Grup Staff Database', 'Grup staf lab untuk Database Lab'),
((SELECT id FROM laboratories WHERE code = 'LAB-MM'), 'Grup Staff Multimedia', 'Grup staf lab untuk Multimedia Lab');

INSERT INTO lab_group_users (group_id, user_id, role_in_group) VALUES
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM users WHERE email = 'staflab@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM users WHERE email = 'staflab.multi@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Jaringan'), (SELECT id FROM users WHERE email = 'staflab.jaringan@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Jaringan'), (SELECT id FROM users WHERE email = 'staflab.multi@example.com'), 'staf_lab'),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Database'), (SELECT id FROM users WHERE email = 'staflab.multi@example.com'), 'staf_lab');

INSERT INTO lab_group_rooms (group_id, room_id) VALUES
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM rooms WHERE code = 'H08-A03')),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM rooms WHERE code = 'H08-A04')),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Programming'), (SELECT id FROM rooms WHERE code = 'H08-B07')),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Jaringan'), (SELECT id FROM rooms WHERE code = 'H08-A02')),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Jaringan'), (SELECT id FROM rooms WHERE code = 'H08-B06')),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Database'), (SELECT id FROM rooms WHERE code = 'H08-C03')),
((SELECT id FROM lab_groups WHERE name = 'Grup Staff Multimedia'), (SELECT id FROM rooms WHERE code = 'H08-C04'));

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