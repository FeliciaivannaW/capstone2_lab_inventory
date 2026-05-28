<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\StafAdminController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoomManagementController;
use App\Http\Controllers\BhpController;
use App\Http\Controllers\MaintenanceController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('signup');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');

Route::middleware('frontend.auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/laboratories', [DashboardController::class, 'laboratories'])
        ->name('laboratories');

    Route::middleware('frontend.role:administrator')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::get('/rooms', [RoomManagementController::class, 'index'])->name('rooms');
        Route::post('/rooms', [RoomManagementController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{id}', [RoomManagementController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{id}', [RoomManagementController::class, 'destroy'])->name('rooms.destroy');
    });

    Route::get('/inventory', [DashboardController::class, 'inventory'])
        ->middleware('frontend.role:administrator,staf_administrasi,staf_laboratorium')
        ->name('inventory');

    Route::get('/bhp', [BhpController::class, 'index'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('bhp');
    Route::post('/bhp', [BhpController::class, 'store'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('bhp.store');
    Route::put('/bhp/{id}', [BhpController::class, 'update'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('bhp.update');
    Route::post('/bhp/{id}/movement', [BhpController::class, 'movement'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('bhp.movement');

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

    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('maintenance');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('maintenance.store');

    Route::post('/api/procurement/{draftId}/items', [ProcurementController::class, 'addItem']);
    Route::delete('/api/procurement/{draftId}/items/{itemId}', [ProcurementController::class, 'deleteItem']);
    Route::patch('/api/procurement/{draftId}/items/{itemId}', [ProcurementController::class, 'updateItem']);
    Route::post('/api/procurement/{draftId}/items/{itemId}/review', [ProcurementController::class, 'reviewItem']);
    Route::post('/api/procurement/{id}/finalize', [ProcurementController::class, 'finalize']);
    Route::post('/api/procurement/{id}/submit', [ProcurementController::class, 'submit']);

    Route::prefix('staf-admin')->middleware('frontend.role:staf_administrasi')->group(function () {
        Route::get('/procurement-approved', [StafAdminController::class, 'procurementApproved'])
            ->name('staf-admin.procurement-approved');
        Route::get('/procurement-approved/{id}', [StafAdminController::class, 'procurementApprovedDetail'])
            ->name('staf-admin.procurement-approved.detail');

        Route::get('/goods-receipt', [StafAdminController::class, 'goodsReceiptIndex'])
            ->name('staf-admin.goods-receipt-index');
        Route::get('/goods-receipt/{draftId}', [StafAdminController::class, 'goodsReceipt'])
            ->name('staf-admin.goods-receipt');
        Route::post('/api/goods-receipt', [StafAdminController::class, 'storeGoodsReceipt'])
            ->name('staf-admin.goods-receipt.store');

        Route::get('/inventory-label', [StafAdminController::class, 'inventoryLabel'])
            ->name('staf-admin.inventory-label');
        Route::get('/inventory-label/{id}/edit', [StafAdminController::class, 'inventoryLabelEdit'])
            ->name('staf-admin.inventory-label.edit');
        Route::put('/inventory-label/{id}', [StafAdminController::class, 'inventoryLabelUpdate'])
            ->name('staf-admin.inventory-label.update');
        Route::post('/api/inventory-label/{id}', [StafAdminController::class, 'inventoryLabelUpdateAjax'])
            ->name('staf-admin.inventory-label.ajax');

        Route::get('/dashboard', [StafAdminController::class, 'dashboard'])
            ->name('staf-admin.dashboard');
        Route::get('/asset-list', [StafAdminController::class, 'assetList'])
            ->name('staf-admin.asset-list');
        Route::get('/asset-timeline/{id}', [StafAdminController::class, 'assetTimeline'])
            ->name('staf-admin.asset-timeline');
        Route::get('/inventaris', [StafAdminController::class, 'inventaris'])
            ->name('staf-admin.inventaris');
    });
});