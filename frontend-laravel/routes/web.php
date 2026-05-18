<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcurementController;

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
    Route::post('/api/procurement/{draftId}/items/{itemId}/review', [ProcurementController::class, 'reviewItem']);
    Route::post('/api/procurement/{id}/finalize', [ProcurementController::class, 'finalize']);
});