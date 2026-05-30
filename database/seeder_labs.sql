USE lab_inventory_db;

-- Tambah floor baru
INSERT INTO floors (building_id, floor_number, name, description)
VALUES
    ((SELECT id FROM buildings WHERE code = 'FTI'), 3, 'Lantai 3', 'Area laboratorium lanjutan'),
    ((SELECT id FROM buildings WHERE code = 'FTI'), 4, 'Lantai 4', 'Area laboratorium khusus');

-- Tambah room_types jika belum ada
INSERT IGNORE INTO room_types (name, description) VALUES
    ('storage', 'Ruangan penyimpanan'),
    ('office', 'Ruangan kantor atau administrasi');

-- Tambah rooms untuk lab-lab baru
INSERT INTO rooms (floor_id, room_type_id, code, name, capacity, description) VALUES
    ((SELECT id FROM floors WHERE floor_number = 3 AND building_id = (SELECT id FROM buildings WHERE code = 'FTI')),
     (SELECT id FROM room_types WHERE name = 'Laboratorium'),
     'FTI-301', 'Programming Lab 1', 40, 'Laboratorium programming 1'),

    ((SELECT id FROM floors WHERE floor_number = 3 AND building_id = (SELECT id FROM buildings WHERE code = 'FTI')),
     (SELECT id FROM room_types WHERE name = 'Laboratorium'),
     'FTI-302', 'Programming Lab 2', 40, 'Laboratorium programming 2'),

    ((SELECT id FROM floors WHERE floor_number = 3 AND building_id = (SELECT id FROM buildings WHERE code = 'FTI')),
     (SELECT id FROM room_types WHERE name = 'Laboratorium'),
     'FTI-303', 'Database Lab', 40, 'Laboratorium basis data'),

    ((SELECT id FROM floors WHERE floor_number = 3 AND building_id = (SELECT id FROM buildings WHERE code = 'FTI')),
     (SELECT id FROM room_types WHERE name = 'Laboratorium'),
     'FTI-304', 'Multimedia Lab', 35, 'Laboratorium multimedia dan desain'),

    ((SELECT id FROM floors WHERE floor_number = 4 AND building_id = (SELECT id FROM buildings WHERE code = 'FTI')),
     (SELECT id FROM room_types WHERE name = 'Laboratorium'),
     'FTI-401', 'Enterprise Lab', 35, 'Laboratorium enterprise dan sistem informasi'),

    ((SELECT id FROM floors WHERE floor_number = 4 AND building_id = (SELECT id FROM buildings WHERE code = 'FTI')),
     (SELECT id FROM room_types WHERE name = 'Laboratorium'),
     'FTI-402', 'AI & Machine Learning Lab', 30, 'Laboratorium kecerdasan buatan');

-- Tambah laboratories baru
INSERT INTO laboratories (room_id, name, code, description) VALUES
    ((SELECT id FROM rooms WHERE code = 'FTI-301'), 'Laboratorium Programming 1', 'LAB-PROG-1', 'Lab pemrograman dasar dan lanjutan'),
    ((SELECT id FROM rooms WHERE code = 'FTI-302'), 'Laboratorium Programming 2', 'LAB-PROG-2', 'Lab pemrograman web dan mobile'),
    ((SELECT id FROM rooms WHERE code = 'FTI-303'), 'Laboratorium Database', 'LAB-DB', 'Lab basis data dan sistem informasi'),
    ((SELECT id FROM rooms WHERE code = 'FTI-304'), 'Laboratorium Multimedia', 'LAB-MM', 'Lab multimedia, desain, dan animasi'),
    ((SELECT id FROM rooms WHERE code = 'FTI-401'), 'Laboratorium Enterprise', 'LAB-ENT', 'Lab sistem enterprise dan ERP'),
    ((SELECT id FROM rooms WHERE code = 'FTI-402'), 'Laboratorium AI & Machine Learning', 'LAB-AI', 'Lab kecerdasan buatan dan machine learning');

-- Update user staf_administrasi yang belum punya lab_id → assign ke lab COMNET
UPDATE users
SET lab_id = (SELECT id FROM laboratories WHERE code = 'COMNET')
WHERE email = 'staf.admin@labventory.test' AND lab_id IS NULL;

-- Update user staf_administrasi example → tetap di lab COMNET (sudah benar)
-- Tambah kepala lab baru untuk lab-lab yang baru (opsional, bisa di-assign nanti oleh admin)
-- Update head_user_id laboratories sesuai kepala lab yang ada
UPDATE laboratories
SET head_user_id = (SELECT id FROM users WHERE email = 'kepala.lab@labventory.test')
WHERE code = 'COMNET' AND head_user_id IS NULL;

UPDATE laboratories
SET head_user_id = (SELECT id FROM users WHERE email = 'kalab@example.com')
WHERE code = 'SE' AND head_user_id IS NULL;
