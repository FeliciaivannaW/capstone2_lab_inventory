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