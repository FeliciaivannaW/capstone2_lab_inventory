<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\StafAdminController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoomManagementController;
use App\Http\Controllers\LaboratoryManagementController;
use App\Http\Controllers\BhpController;
use App\Http\Controllers\MaintenanceController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('signup');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('frontend.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('frontend.role:administrator')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::get('/rooms', [RoomManagementController::class, 'index'])->name('rooms');
        Route::post('/rooms', [RoomManagementController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{id}', [RoomManagementController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{id}', [RoomManagementController::class, 'destroy'])->name('rooms.destroy');

        Route::post('/buildings', [RoomManagementController::class, 'storeBuilding'])->name('buildings.store');
        Route::post('/floors', [RoomManagementController::class, 'storeFloor'])->name('floors.store');
        Route::post('/room-types', [RoomManagementController::class, 'storeRoomType'])->name('room-types.store');

        Route::put('/buildings/{id}', [RoomManagementController::class, 'updateBuilding'])->name('buildings.update');
        Route::put('/floors/{id}', [RoomManagementController::class, 'updateFloor'])->name('floors.update');
        Route::put('/room-types/{id}', [RoomManagementController::class, 'updateRoomType'])->name('room-types.update');

        Route::delete('/buildings/{id}', [RoomManagementController::class, 'destroyBuilding'])->name('buildings.destroy');
        Route::delete('/floors/{id}', [RoomManagementController::class, 'destroyFloor'])->name('floors.destroy');
        Route::delete('/room-types/{id}', [RoomManagementController::class, 'destroyRoomType'])->name('room-types.destroy');

        Route::get('/laboratories', [LaboratoryManagementController::class, 'index'])->name('laboratories');
        Route::post('/laboratories', [LaboratoryManagementController::class, 'store'])->name('laboratories.store');
        Route::put('/laboratories/{id}', [LaboratoryManagementController::class, 'update'])->name('laboratories.update');
        Route::delete('/laboratories/{id}', [LaboratoryManagementController::class, 'destroy'])->name('laboratories.destroy');

        Route::post('/lab-groups', [LaboratoryManagementController::class, 'storeGroup'])->name('lab-groups.store');
        Route::put('/lab-groups/{id}', [LaboratoryManagementController::class, 'updateGroup'])->name('lab-groups.update');
        Route::delete('/lab-groups/{id}', [LaboratoryManagementController::class, 'destroyGroup'])->name('lab-groups.destroy');
        Route::post('/lab-groups/{groupId}/users', [LaboratoryManagementController::class, 'addGroupUser'])->name('lab-groups.users.store');
        Route::post('/lab-groups/{groupId}/rooms', [LaboratoryManagementController::class, 'addGroupRoom'])->name('lab-groups.rooms.store');
        Route::get('/lab-groups/{id}/details', [LaboratoryManagementController::class, 'getGroupDetails'])->name('lab-groups.details');
        Route::delete('/lab-groups/{groupId}/users/{userId}', [LaboratoryManagementController::class, 'destroyGroupUser'])->name('lab-groups.users.destroy');
        Route::delete('/lab-groups/{groupId}/rooms/{roomId}', [LaboratoryManagementController::class, 'destroyGroupRoom'])->name('lab-groups.rooms.destroy');
    });

    Route::get('/inventory', [DashboardController::class, 'inventory'])
        ->middleware('frontend.role:administrator,staf_administrasi,staf_laboratorium,kepala_laboratorium,ketua_program_studi')
        ->name('inventory');

    Route::get('/inventory/history', [DashboardController::class, 'inventoryHistory'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('inventory.history');

    Route::match(['put', 'patch'], '/inventory/{id}/condition', [DashboardController::class, 'updateInventoryCondition'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('inventory.condition.update');

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

    Route::get('/bhp/{id}/movements', [BhpController::class, 'getMovements'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('bhp.movements');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('maintenance');

    Route::post('/maintenance', [MaintenanceController::class, 'store'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('maintenance.store');

    Route::put('/maintenance/{id}', [MaintenanceController::class, 'update'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('maintenance.update');

    Route::delete('/maintenance/{id}', [MaintenanceController::class, 'destroy'])
        ->middleware('frontend.role:staf_laboratorium')
        ->name('maintenance.destroy');

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

    Route::post('/api/procurement/{draftId}/items', [ProcurementController::class, 'addItem']);
    Route::delete('/api/procurement/{draftId}/items/{itemId}', [ProcurementController::class, 'deleteItem']);
    Route::patch('/api/procurement/{draftId}/items/{itemId}', [ProcurementController::class, 'updateItem']);
    Route::post('/api/procurement/{draftId}/items/{itemId}/review', [ProcurementController::class, 'reviewItem']);
    Route::post('/api/procurement/{id}/finalize', [ProcurementController::class, 'finalize']);
    Route::post('/api/procurement/{id}/return', [ProcurementController::class, 'returnDraft']);
    Route::post('/api/procurement/{id}/submit', [ProcurementController::class, 'submit']);

    Route::middleware('frontend.role:staf_administrasi')->group(function () {
        Route::get('/procurement-approved', [StafAdminController::class, 'procurementApproved'])->name('staf-admin.procurement-approved');
        Route::get('/procurement-approved/{id}', [StafAdminController::class, 'procurementApprovedDetail'])->name('staf-admin.procurement-approved.detail');

        Route::get('/goods-receipt', [StafAdminController::class, 'goodsReceiptIndex'])->name('staf-admin.goods-receipt-index');
        Route::get('/goods-receipt/{draftId}', [StafAdminController::class, 'goodsReceipt'])->name('staf-admin.goods-receipt');
        Route::post('/api/goods-receipt', [StafAdminController::class, 'storeGoodsReceipt'])->name('staf-admin.goods-receipt.store');

        Route::get('/api/label-check', [StafAdminController::class, 'labelCheck'])->name('staf-admin.label-check');
        Route::get('/api/next-label', [StafAdminController::class, 'nextLabelApi'])->name('staf-admin.next-label');
        Route::get('/api/inventory/assets', [StafAdminController::class, 'inventoryAssetsApi'])->name('staf-admin.inventory-assets-api');
        Route::post('/api/inventory/batches/{id}/label-all', [StafAdminController::class, 'labelAllAjax'])->name('staf-admin.label-all.ajax');
        Route::get('/print-label', [StafAdminController::class, 'printLabel'])->name('staf-admin.print-label');
        Route::get('/inventory-label', [StafAdminController::class, 'inventoryLabel'])->name('staf-admin.inventory-label');
        Route::get('/inventory-label/{id}/edit', [StafAdminController::class, 'inventoryLabelEdit'])->name('staf-admin.inventory-label.edit');
        Route::put('/inventory-label/{id}', [StafAdminController::class, 'inventoryLabelUpdate'])->name('staf-admin.inventory-label.update');
        Route::post('/api/inventory-label/{id}', [StafAdminController::class, 'inventoryLabelUpdateAjax'])->name('staf-admin.inventory-label.ajax');
        Route::get('/api/rooms', [StafAdminController::class, 'roomsApi'])->name('staf-admin.rooms-api');


        Route::get('/asset-list', [StafAdminController::class, 'assetList'])->name('staf-admin.asset-list');
        Route::get('/asset-timeline/{id}', [StafAdminController::class, 'assetTimeline'])->name('staf-admin.asset-timeline');
        Route::get('/inventaris', [StafAdminController::class, 'inventaris'])->name('staf-admin.inventaris');
    });
});