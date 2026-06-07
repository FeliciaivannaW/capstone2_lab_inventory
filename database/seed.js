/**
 * ============================================================
 * LABVENTORY — TEST SEEDER
 * ============================================================
 * Covers all roles & all Staf Administrasi features:
 *   Phase 3A: Draf Disetujui (finalized draft + approved items)
 *   Phase 3B: Penerimaan Barang (partial + full goods receipt)
 *   Phase 3C: Update Label & Foto (assets received, some labeled)
 *   Phase 3D: Semua Inventaris (varied status/condition)
 *
 * Password semua user: password123
 *
 * Run: node database/seed.js
 * ============================================================
 */

require('dotenv').config({ path: './backend-node/.env' });
const mysql  = require('mysql2/promise');
const bcrypt = require('bcrypt');

const DB_CONFIG = {
  host:     process.env.DB_HOST     || 'localhost',
  user:     process.env.DB_USER     || 'root',
  password: process.env.DB_PASS     || '',
  database: process.env.DB_NAME     || 'lab_inventory_db',
  port:     process.env.DB_PORT     || 3306,
};

const PLAIN_PASSWORD = 'password123';

// ─── Helpers ────────────────────────────────────────────────
async function hash(pw) { return bcrypt.hash(pw, 10); }
async function q(conn, sql, params = []) {
  const [result] = await conn.execute(sql, params);
  return result;
}
async function insertId(conn, sql, params = []) {
  const [result] = await conn.execute(sql, params);
  return result.insertId;
}

// ─── Main ────────────────────────────────────────────────────
async function seed() {
  const conn = await mysql.createConnection(DB_CONFIG);
  console.log('✓ Terhubung ke database:', DB_CONFIG.database);

  try {
    await conn.execute('SET FOREIGN_KEY_CHECKS = 0');

    // ════════════════════════════════════════════════════════
    // 0. TRUNCATE (safe reset)
    // ════════════════════════════════════════════════════════
    const tables = [
      'bhp_stock_movements','bhp_stocks','maintenance_logs',
      'asset_disposals','asset_condition_logs','inventory_assets',
      'goods_receipts','procurement_items','procurement_drafts',
      'item_catalogs','item_categories',
      'lab_group_rooms','lab_group_users','lab_groups',
      'users','laboratories','rooms','room_types','floors','buildings'
    ];
    for (const t of tables) {
      await conn.execute(`TRUNCATE TABLE \`${t}\``);
      console.log(`  TRUNCATE ${t}`);
    }

    await conn.execute('SET FOREIGN_KEY_CHECKS = 1');
    console.log('\n--- Seeding data ---\n');

    const pw = await hash(PLAIN_PASSWORD);

    // ════════════════════════════════════════════════════════
    // 1. BUILDINGS + FLOORS + ROOM TYPES + ROOMS
    // ════════════════════════════════════════════════════════
    const buildingId = await insertId(conn,
      `INSERT INTO buildings (name, code, address) VALUES (?, ?, ?)`,
      ['Gedung Teknik Informatika', 'GTI', 'Jl. Kampus Raya No.1']
    );

    const floorId = await insertId(conn,
      `INSERT INTO floors (building_id, floor_number, name) VALUES (?, ?, ?)`,
      [buildingId, 2, 'Lantai 2']
    );

    const roomTypeId = await insertId(conn,
      `INSERT INTO room_types (name, description) VALUES (?, ?)`,
      ['Laboratorium', 'Ruangan laboratorium komputer']
    );

    const roomId1 = await insertId(conn,
      `INSERT INTO rooms (floor_id, room_type_id, code, name, capacity) VALUES (?, ?, ?, ?, ?)`,
      [floorId, roomTypeId, 'LAB-CN-01', 'Lab Computer Network 1', 30]
    );
    const roomId2 = await insertId(conn,
      `INSERT INTO rooms (floor_id, room_type_id, code, name, capacity) VALUES (?, ?, ?, ?, ?)`,
      [floorId, roomTypeId, 'LAB-SE-01', 'Lab Software Engineering 1', 30]
    );
    console.log('✓ Buildings, Floors, Room Types, Rooms');

    // ════════════════════════════════════════════════════════
    // 2. LABORATORIES
    // ════════════════════════════════════════════════════════
    const labId1 = await insertId(conn,
      `INSERT INTO laboratories (name, code, room_id, description) VALUES (?, ?, ?, ?)`,
      ['Laboratorium Computer Network', 'COMNET', roomId1, 'Lab jaringan komputer']
    );
    const labId2 = await insertId(conn,
      `INSERT INTO laboratories (name, code, room_id, description) VALUES (?, ?, ?, ?)`,
      ['Laboratorium Software Engineering', 'SE', roomId2, 'Lab pengembangan perangkat lunak']
    );
    console.log('✓ Laboratories (COMNET, SE)');

    // ════════════════════════════════════════════════════════
    // 3. USERS — satu per role
    // Roles: 1=admin, 2=kepala_lab, 3=kaprodi, 4=staf_admin, 5=staf_lab
    // ════════════════════════════════════════════════════════
    const adminId = await insertId(conn,
      `INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password) VALUES (?, NULL, ?, ?, ?, ?)`,
      [1, 'Administrator Sistem', 'ADM001', 'admin@labventory.test', pw]
    );
    const kepalaLabId = await insertId(conn,
      `INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password) VALUES (?, ?, ?, ?, ?, ?)`,
      [2, labId1, 'Dr. Kepala Laboratorium', 'KAL001', 'kepala.lab@labventory.test', pw]
    );
    const kaprodiId = await insertId(conn,
      `INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password) VALUES (?, NULL, ?, ?, ?, ?)`,
      [3, 'Prof. Ketua Program Studi', 'KPS001', 'kaprodi@labventory.test', pw]
    );
    const stafAdminId = await insertId(conn,
      `INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password) VALUES (?, NULL, ?, ?, ?, ?)`,
      [4, 'Budi Staf Administrasi', 'STF001', 'staf.admin@labventory.test', pw]
    );
    const stafLabId = await insertId(conn,
      `INSERT INTO users (role_id, lab_id, name, nrp_nip, email, password) VALUES (?, ?, ?, ?, ?, ?)`,
      [5, labId1, 'Andi Staf Laboratorium', 'STL001', 'staf.lab@labventory.test', pw]
    );
    console.log('✓ Users (5 roles)');
    console.log('  admin@labventory.test         → administrator');
    console.log('  kepala.lab@labventory.test    → kepala_laboratorium');
    console.log('  kaprodi@labventory.test       → ketua_program_studi');
    console.log('  staf.admin@labventory.test    → staf_administrasi');
    console.log('  staf.lab@labventory.test      → staf_laboratorium');
    console.log('  password: password123');

    // ════════════════════════════════════════════════════════
    // 4. LAB GROUP ACCESS — demo akses staf laboratorium
    //    Data ini dipakai supaya modal User dan dashboard Staf Lab
    //    langsung menampilkan grup serta lab/ruangan yang dikelola.
    // ════════════════════════════════════════════════════════
    const groupComnetId = await insertId(conn,
      `INSERT INTO lab_groups (laboratory_id, name, description) VALUES (?, ?, ?)`,
      [labId1, 'Grup Staff Computer Network', 'Grup staf lab untuk mengelola Laboratorium Computer Network']
    );
    const groupSeId = await insertId(conn,
      `INSERT INTO lab_groups (laboratory_id, name, description) VALUES (?, ?, ?)`,
      [labId2, 'Grup Staff Software Engineering', 'Grup staf lab untuk mengelola Laboratorium Software Engineering']
    );

    await conn.execute(
      `INSERT INTO lab_group_users (group_id, user_id, role_in_group) VALUES (?, ?, 'staf_lab')`,
      [groupComnetId, stafLabId]
    );

    await conn.execute(
      `INSERT INTO lab_group_rooms (group_id, room_id) VALUES (?, ?), (?, ?)`,
      [groupComnetId, roomId1, groupSeId, roomId2]
    );

    console.log('✓ Lab Groups & akses Staf Laboratorium');
    console.log('  Grup Staff Computer Network      → Laboratorium Computer Network / LAB-CN-01');
    console.log('  Grup Staff Software Engineering  → Laboratorium Software Engineering / LAB-SE-01');

    // ════════════════════════════════════════════════════════
    // 5. ITEM CATEGORIES + ITEM CATALOGS
    // ════════════════════════════════════════════════════════
    const catHardware = await insertId(conn,
      `INSERT INTO item_categories (name, description) VALUES (?, ?)`,
      ['Hardware Jaringan', 'Perangkat keras jaringan']
    );
    const catKomputer = await insertId(conn,
      `INSERT INTO item_categories (name, description) VALUES (?, ?)`,
      ['Komputer & Laptop', 'PC dan laptop lab']
    );
    const catBHP = await insertId(conn,
      `INSERT INTO item_categories (name, description) VALUES (?, ?)`,
      ['Bahan Habis Pakai', 'Bahan habis pakai lab']
    );
    console.log('✓ Item Categories');

    // Inventory catalogs
    const itemSwitch = await insertId(conn,
      `INSERT INTO item_catalogs (category_id, name, type, unit, description) VALUES (?, ?, ?, ?, ?)`,
      [catHardware, 'Managed Switch 24-Port', 'inventory', 'unit', 'Cisco — 24-port GbE, Layer 2']
    );
    const itemPC = await insertId(conn,
      `INSERT INTO item_catalogs (category_id, name, type, unit, description) VALUES (?, ?, ?, ?, ?)`,
      [catKomputer, 'PC Desktop Intel i7', 'inventory', 'unit', 'Acer — i7-12700, 16GB RAM, 512GB SSD']
    );
    const itemLaptop = await insertId(conn,
      `INSERT INTO item_catalogs (category_id, name, type, unit, description) VALUES (?, ?, ?, ?, ?)`,
      [catKomputer, 'Laptop Lenovo ThinkPad', 'inventory', 'unit', 'Lenovo — i5-1240P, 8GB RAM, 256GB SSD']
    );
    // BHP catalogs
    const itemKertas = await insertId(conn,
      `INSERT INTO item_catalogs (category_id, name, type, unit, description) VALUES (?, ?, ?, ?, ?)`,
      [catBHP, 'Kertas HVS A4 80gsm', 'bhp', 'rim', 'Sinar Dunia — HVS A4 80gsm']
    );
    const itemTinta = await insertId(conn,
      `INSERT INTO item_catalogs (category_id, name, type, unit, description) VALUES (?, ?, ?, ?, ?)`,
      [catBHP, 'Tinta Printer Epson 664', 'bhp', 'botol', 'Epson — Tinta Printer 664']
    );
    console.log('✓ Item Catalogs (3 inventory, 2 BHP)');

    // ════════════════════════════════════════════════════════
    // 5. PROCUREMENT DRAFTS
    //    Draft A: finalized (siap penerimaan) — LAB COMNET
    //    Draft B: submitted (menunggu review kaprodi)
    //    Draft C: draft (masih dibuat)
    // ════════════════════════════════════════════════════════

    // Draft A — FINALIZED (trigger Phase 3A, 3B, 3C, 3D)
    const draftAId = await insertId(conn, `
      INSERT INTO procurement_drafts
        (lab_id, created_by, finalized_by, title, budget_year, status, is_locked, submitted_at, finalized_at)
      VALUES (?, ?, ?, ?, ?, 'finalized', 1, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 5 DAY)
    `, [labId1, kepalaLabId, kaprodiId, 'Pengadaan Peralatan Lab COMNET 2025', 2025]);

    // Draft B — SUBMITTED (untuk test kaprodi review)
    const draftBId = await insertId(conn, `
      INSERT INTO procurement_drafts
        (lab_id, created_by, title, budget_year, status, is_locked, submitted_at)
      VALUES (?, ?, ?, ?, 'submitted', 0, NOW() - INTERVAL 2 DAY)
    `, [labId2, kepalaLabId, 'Pengadaan Lab SE — Laptop 2025', 2025]);

    // Draft C — DRAFT (untuk test submit)
    const draftCId = await insertId(conn, `
      INSERT INTO procurement_drafts
        (lab_id, created_by, title, budget_year, status, is_locked)
      VALUES (?, ?, ?, ?, 'draft', 0)
    `, [labId1, kepalaLabId, 'Pengadaan Bahan Habis Pakai Q4 2025', 2025]);

    console.log('✓ Procurement Drafts (A=finalized, B=submitted, C=draft)');

    // ════════════════════════════════════════════════════════
    // 6. PROCUREMENT ITEMS
    // ════════════════════════════════════════════════════════

    // Draft A items (finalized, all approved)
    const itemA1 = await insertId(conn, `
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status, reviewed_by, reviewed_at, review_note)
      VALUES (?, ?, ?, 'inventory', ?, ?, 'approved', ?, NOW() - INTERVAL 5 DAY, 'Sesuai kebutuhan lab')
    `, [draftAId, itemSwitch, 'Managed Switch 24-Port', 2, 15000000, kaprodiId]);

    const itemA2 = await insertId(conn, `
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status, reviewed_by, reviewed_at, review_note)
      VALUES (?, ?, ?, 'inventory', ?, ?, 'approved', ?, NOW() - INTERVAL 5 DAY, 'Disetujui')
    `, [draftAId, itemPC, 'PC Desktop Intel i7', 3, 12000000, kaprodiId]);

    const itemA3 = await insertId(conn, `
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status, reviewed_by, reviewed_at)
      VALUES (?, ?, ?, 'bhp', ?, ?, 'approved', ?, NOW() - INTERVAL 5 DAY)
    `, [draftAId, itemKertas, 'Kertas HVS A4 80gsm', 10, 65000, kaprodiId]);

    const itemA4 = await insertId(conn, `
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status, reviewed_by, reviewed_at, review_note)
      VALUES (?, ?, ?, 'bhp', ?, ?, 'rejected', ?, NOW() - INTERVAL 5 DAY, 'Anggaran tidak mencukupi')
    `, [draftAId, itemTinta, 'Tinta Printer Epson 664', 5, 75000, kaprodiId]);

    // Draft B items (submitted, pending review)
    const itemB1 = await insertId(conn, `
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status)
      VALUES (?, ?, ?, 'inventory', ?, ?, 'pending')
    `, [draftBId, itemLaptop, 'Laptop Lenovo ThinkPad', 5, 11000000]);

    const itemB2 = await insertId(conn, `
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status)
      VALUES (?, ?, ?, 'bhp', ?, ?, 'pending')
    `, [draftBId, itemTinta, 'Tinta Printer Epson 664', 12, 75000]);

    // Draft C items (draft, editable)
    await conn.execute(`
      INSERT INTO procurement_items
        (draft_id, item_catalog_id, item_name, item_type, quantity, estimated_price, review_status)
      VALUES (?, ?, ?, 'bhp', ?, ?, 'pending')
    `, [draftCId, itemKertas, 'Kertas HVS A4 80gsm', 20, 65000]);

    console.log('✓ Procurement Items');

    // ════════════════════════════════════════════════════════
    // 7. GOODS RECEIPTS
    //    itemA1 (switch qty=2): 1 diterima → PARTIAL → test 3B
    //    itemA2 (PC qty=3):     3 diterima → FULL   → inventory_assets dibuat
    //    itemA3 (kertas qty=10): 6 diterima → PARTIAL → bhp_stocks dibuat
    // ════════════════════════════════════════════════════════

    // itemA1 — 1 switch diterima (partial, qty=2, received=1)
    const receiptA1 = await insertId(conn, `
      INSERT INTO goods_receipts (procurement_item_id, received_by, received_date, quantity_received, note)
      VALUES (?, ?, CURDATE() - INTERVAL 3 DAY, 1, 'Pengiriman pertama — 1 unit dari 2')
    `, [itemA1, stafAdminId]);

    // Buat 1 inventory_asset untuk 1 switch yang diterima
    const assetSwitch1 = await insertId(conn, `
      INSERT INTO inventory_assets
        (item_catalog_id, procurement_item_id, receipt_id, asset_code, received_date, status, asset_condition)
      VALUES (?, ?, ?, ?, CURDATE() - INTERVAL 3 DAY, 'received', 'baik')
    `, [itemSwitch, itemA1, receiptA1, 'INV-COMNET-2025-001']);

    // itemA2 — 3 PC semua diterima (full, qty=3)
    const receiptA2 = await insertId(conn, `
      INSERT INTO goods_receipts (procurement_item_id, received_by, received_date, quantity_received, note)
      VALUES (?, ?, CURDATE() - INTERVAL 3 DAY, 3, 'Semua unit PC diterima lengkap')
    `, [itemA2, stafAdminId]);

    // Buat 3 inventory_assets untuk PC
    // PC 1: sudah berlabel (untuk test lifecycle complete)
    const assetPC1 = await insertId(conn, `
      INSERT INTO inventory_assets
        (item_catalog_id, procurement_item_id, receipt_id, room_id, asset_code, label_number, received_date, status, asset_condition)
      VALUES (?, ?, ?, ?, ?, ?, CURDATE() - INTERVAL 3 DAY, 'labeled', 'baik')
    `, [itemPC, itemA2, receiptA2, roomId1, 'INV-COMNET-2025-002', 'LAB-CN-PC-001']);

    // Tambah condition log untuk PC 1 (sudah dilabel)
    await conn.execute(`
      INSERT INTO asset_condition_logs (inventory_asset_id, updated_by, old_condition, new_condition, note, updated_at)
      VALUES (?, ?, 'baik', 'baik', 'Label assigned', NOW() - INTERVAL 1 DAY)
    `, [assetPC1, stafAdminId]);

    // PC 2: received, belum berlabel
    const assetPC2 = await insertId(conn, `
      INSERT INTO inventory_assets
        (item_catalog_id, procurement_item_id, receipt_id, asset_code, received_date, status, asset_condition)
      VALUES (?, ?, ?, ?, CURDATE() - INTERVAL 3 DAY, 'received', 'baik')
    `, [itemPC, itemA2, receiptA2, 'INV-COMNET-2025-003']);

    // PC 3: received, kondisi rusak ringan
    const assetPC3 = await insertId(conn, `
      INSERT INTO inventory_assets
        (item_catalog_id, procurement_item_id, receipt_id, asset_code, received_date, status, asset_condition, notes)
      VALUES (?, ?, ?, ?, CURDATE() - INTERVAL 3 DAY, 'received', 'rusak_ringan', 'Layar sedikit retak, masih fungsional')
    `, [itemPC, itemA2, receiptA2, 'INV-COMNET-2025-004']);

    console.log('✓ Goods Receipts (switch=partial, PC=full)');
    console.log('✓ Inventory Assets (1 switch-received, 1 PC-labeled, 2 PC-received)');

    // ════════════════════════════════════════════════════════
    // 8. BHP STOCKS (dari itemA3 — kertas partial)
    // ════════════════════════════════════════════════════════

    const receiptA3 = await insertId(conn, `
      INSERT INTO goods_receipts (procurement_item_id, received_by, received_date, quantity_received, note)
      VALUES (?, ?, CURDATE() - INTERVAL 3 DAY, 6, 'Pengiriman pertama 6 rim dari 10')
    `, [itemA3, stafAdminId]);

    const stockKertasId = await insertId(conn, `
      INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit)
      VALUES (?, ?, ?, ?, ?)
    `, [labId1, itemKertas, 6, 5, 'rim']);

    await conn.execute(`
      INSERT INTO bhp_stock_movements
        (stock_id, procurement_item_id, receipt_id, performed_by, movement_type, quantity, movement_date, note)
      VALUES (?, ?, ?, ?, 'in', 6, NOW() - INTERVAL 3 DAY, 'Penerimaan dari draf pengadaan')
    `, [stockKertasId, itemA3, receiptA3, stafAdminId]);

    // Stock tinta (existing stock tanpa procurement — simulasi stok awal)
    const stockTintaId = await insertId(conn, `
      INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit)
      VALUES (?, ?, ?, ?, ?)
    `, [labId1, itemTinta, 3, 5, 'botol']);

    // Stock kertas Lab SE
    await conn.execute(`
      INSERT INTO bhp_stocks (lab_id, item_catalog_id, current_stock, minimum_stock, unit)
      VALUES (?, ?, ?, ?, ?)
    `, [labId2, itemKertas, 15, 10, 'rim']);

    console.log('✓ BHP Stocks & Movements');

    // ════════════════════════════════════════════════════════
    // SUMMARY
    // ════════════════════════════════════════════════════════
    console.log('\n═══════════════════════════════════════════════');
    console.log('✅ SEEDER SELESAI!');
    console.log('═══════════════════════════════════════════════\n');
    console.log('👤 AKUN LOGIN (semua password: password123)');
    console.log('─────────────────────────────────────────────');
    console.log('admin@labventory.test       → administrator');
    console.log('kepala.lab@labventory.test  → kepala_laboratorium');
    console.log('kaprodi@labventory.test     → ketua_program_studi');
    console.log('staf.admin@labventory.test  → staf_administrasi  ← FOKUS TEST');
    console.log('staf.lab@labventory.test    → staf_laboratorium');
    console.log('\n📋 DATA YANG SUDAH DISIAPKAN');
    console.log('─────────────────────────────────────────────');
    console.log('2 Laboratorium: COMNET, SE');
    console.log('2 Ruangan: LAB-CN-01, LAB-SE-01');
    console.log('5 Item Katalog: 3 inventory + 2 BHP');
    console.log('\n📦 PROCUREMENT DRAFTS');
    console.log('─────────────────────────────────────────────');
    console.log(`Draft A (ID: ${draftAId}) — FINALIZED → Buka di "Draf Disetujui"`);
    console.log('  • Switch 24-port × 2  → approved, 1 diterima (PARTIAL)');
    console.log('  • PC Desktop × 3      → approved, 3 diterima (FULL, ada assets)');
    console.log('  • Kertas A4 × 10 rim  → approved, 6 diterima (PARTIAL, bhp_stocks)');
    console.log('  • Tinta Epson × 5     → REJECTED (tidak diproses)');
    console.log(`Draft B (ID: ${draftBId}) — SUBMITTED → Test Kaprodi review`);
    console.log(`Draft C (ID: ${draftCId}) — DRAFT      → Test submit ke Kaprodi`);
    console.log('\n🏷  INVENTORY ASSETS (status)');
    console.log('─────────────────────────────────────────────');
    console.log('INV-COMNET-2025-001 → Switch (received, belum dilabel)');
    console.log('INV-COMNET-2025-002 → PC [LAB-CN-PC-001] (labeled, ada di Lab CN-01)');
    console.log('INV-COMNET-2025-003 → PC (received, belum dilabel)');
    console.log('INV-COMNET-2025-004 → PC (received, kondisi rusak_ringan)');
    console.log('\n📊 BHP STOCKS');
    console.log('─────────────────────────────────────────────');
    console.log(`COMNET: Kertas HVS 6 rim (min: 5)  → cukup`);
    console.log(`COMNET: Tinta Epson 3 botol (min: 5) → STOK RENDAH ⚠`);
    console.log(`SE:     Kertas HVS 15 rim (min: 10) → cukup`);
    console.log('═══════════════════════════════════════════════\n');

  } catch (err) {
    console.error('\n❌ ERROR saat seeding:', err.message);
    console.error(err);
  } finally {
    await conn.end();
    process.exit(0);
  }
}

seed();
