<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/laboratories', [DashboardController::class, 'laboratories'])->name('laboratories');
Route::get('/rooms', [DashboardController::class, 'rooms'])->name('rooms');
Route::get('/inventory', [DashboardController::class, 'inventory'])->name('inventory');
Route::get('/bhp', [DashboardController::class, 'bhp'])->name('bhp');
Route::get('/procurement', [DashboardController::class, 'procurement'])->name('procurement');
Route::get('/maintenance', [DashboardController::class, 'maintenance'])->name('maintenance');