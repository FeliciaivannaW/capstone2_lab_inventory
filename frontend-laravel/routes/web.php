<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\StafAdminController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('signup');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');

Route::middleware('frontend.auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/laboratories', [DashboardController::class, 'laboratories'])
        ->name('laboratories');

    Route::get('/rooms', [DashboardController::class, 'rooms'])
        ->middleware('frontend.role:administrator')
        ->name('rooms');

    Route::get('/inventory', [DashboardController::class, 'inventory'])
        ->middleware('frontend.role:administrator,staf_administrasi,staf_laboratorium')
        ->name('inventory');

    Route::get('/bhp', [DashboardController::class, 'bhp'])
        ->middleware('frontend.role:administrator,staf_laboratorium')
        ->name('bhp');

    Route::get('/procurement', [DashboardController::class, 'procurement'])
        ->middleware('frontend.role:administrator,kepala_laboratorium,ketua_program_studi,staf_administrasi')
        ->name('procurement');

    Route::get('/procurement/create', [ProcurementController::class, 'create'])
        ->middleware('frontend.role:kepala_laboratorium,staf_administrasi')
        ->name('procurement.create');

    Route::post('/procurement', [ProcurementController::class, 'store'])
        ->middleware('frontend.role:kepala_laboratorium,staf_administrasi')
        ->name('procurement.store');

    Route::get('/procurement/{id}', [ProcurementController::class, 'show'])
        ->middleware('frontend.role:administrator,kepala_laboratorium,ketua_program_studi,staf_administrasi')
        ->name('procurement.show');

    Route::get('/procurement/{id}/edit', [ProcurementController::class, 'edit'])
        ->middleware('frontend.role:kepala_laboratorium,staf_administrasi')
        ->name('procurement.edit');

    Route::put('/procurement/{id}', [ProcurementController::class, 'update'])
        ->middleware('frontend.role:kepala_laboratorium,staf_administrasi')
        ->name('procurement.update');

    Route::delete('/procurement/{id}', [ProcurementController::class, 'destroy'])
        ->middleware('frontend.role:kepala_laboratorium,staf_administrasi')
        ->name('procurement.destroy');

    Route::get('/maintenance', [DashboardController::class, 'maintenance'])
        ->middleware('frontend.role:administrator,staf_laboratorium')
        ->name('maintenance');

    // API Routes for AJAX/Frontend to Backend Proxy
    Route::post('/api/procurement/{draftId}/items', [ProcurementController::class, 'addItem']);
    Route::delete('/api/procurement/{draftId}/items/{itemId}', [ProcurementController::class, 'deleteItem']);
    Route::patch('/api/procurement/{draftId}/items/{itemId}', [ProcurementController::class, 'updateItem']);
    Route::post('/api/procurement/{draftId}/items/{itemId}/review', [ProcurementController::class, 'reviewItem']);
    Route::post('/api/procurement/{id}/finalize', [ProcurementController::class, 'finalize']);
    Route::post('/api/procurement/{id}/submit', [ProcurementController::class, 'submit']);

    // ============================================================
    // STAF ADMINISTRASI ROUTES
    // ============================================================
    Route::prefix('staf-admin')->middleware('frontend.role:staf_administrasi')->group(function () {

        // Fitur 1: Lihat Draf Pengadaan yang Disetujui Kaprodi
        Route::get('/procurement-approved', [StafAdminController::class, 'procurementApproved'])
            ->name('staf-admin.procurement-approved');
        Route::get('/procurement-approved/{id}', [StafAdminController::class, 'procurementApprovedDetail'])
            ->name('staf-admin.procurement-approved.detail');

        // Fitur 2: Input Tanggal Penerimaan Barang
        Route::get('/goods-receipt', [StafAdminController::class, 'goodsReceiptIndex'])
            ->name('staf-admin.goods-receipt-index');
        Route::get('/goods-receipt/{draftId}', [StafAdminController::class, 'goodsReceipt'])
            ->name('staf-admin.goods-receipt');
        Route::post('/api/goods-receipt', [StafAdminController::class, 'storeGoodsReceipt'])
            ->name('staf-admin.goods-receipt.store');

        // Fitur 3: Update Nomor Label & Foto QR/Barcode
        Route::get('/inventory-label', [StafAdminController::class, 'inventoryLabel'])
            ->name('staf-admin.inventory-label');
        Route::get('/inventory-label/{id}/edit', [StafAdminController::class, 'inventoryLabelEdit'])
            ->name('staf-admin.inventory-label.edit');
        Route::put('/inventory-label/{id}', [StafAdminController::class, 'inventoryLabelUpdate'])
            ->name('staf-admin.inventory-label.update');
        // AJAX label update (JSON, no file)
        Route::post('/api/inventory-label/{id}', [StafAdminController::class, 'inventoryLabelUpdateAjax'])
            ->name('staf-admin.inventory-label.ajax');

        // Fitur 4: Dashboard Ringkasan (Statistik)
        Route::get('/dashboard', [StafAdminController::class, 'dashboard'])
            ->name('staf-admin.dashboard');

        // Fitur 5: Pelacakan Siklus Barang
        Route::get('/asset-list', [StafAdminController::class, 'assetList'])
            ->name('staf-admin.asset-list');
        Route::get('/asset-timeline/{id}', [StafAdminController::class, 'assetTimeline'])
            ->name('staf-admin.asset-timeline');

        // Fitur 6: Semua Inventaris (read-only, dedicated page)
        Route::get('/inventaris', [StafAdminController::class, 'inventaris'])
            ->name('staf-admin.inventaris');
    });
});