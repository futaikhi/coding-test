<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockMutationController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD Role Management - Bisa diakses semua role setelah login
    Route::resource('roles', RoleController::class);

    // CRUD User Account - Hanya untuk Administrator
    Route::middleware(['role:Administrator'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // CRUD Material
    Route::resource('materials', MaterialController::class);
    Route::post('/materials/export', [MaterialController::class, 'triggerExport']);
    Route::get('/materials/export/check', [MaterialController::class, 'checkExportStatus']);
    Route::post('/materials/import/upload', [MaterialController::class, 'importUpload']);
    Route::post('/materials/import/process', [MaterialController::class, 'importProcess']);
    Route::get('/materials/import/progress', [MaterialController::class, 'importProgress']);

    // CRUD Category
    Route::resource('categories', CategoryController::class);
    Route::post('/categories/export', [CategoryController::class, 'triggerExport']);
    Route::get('/categories/export/check', [CategoryController::class, 'checkExportStatus']);
    Route::post('/categories/import/upload', [CategoryController::class, 'importUpload']);
    Route::post('/categories/import/process', [CategoryController::class, 'importProcess']);
    Route::get('/categories/import/progress', [CategoryController::class, 'importProgress']);

    // CRUD Stock Mutation
    Route::resource('stock-mutations', StockMutationController::class);
    Route::post('/stock-mutations/export', [StockMutationController::class, 'triggerExport']);
    Route::get('/stock-mutations/export/check', [StockMutationController::class, 'checkExportStatus']);
    Route::post('/stock-mutations/import/upload', [StockMutationController::class, 'importUpload']);
    Route::post('/stock-mutations/import/process', [StockMutationController::class, 'importProcess']);
    Route::get('/stock-mutations/import/progress', [StockMutationController::class, 'importProgress']);

    // Endpoint API untuk remote search Select2 (Mengembalikan format { value, label })
    Route::get('/api/categories-search', [CategoryController::class, 'searchApi']);
    Route::get('/api/materials-search', [MaterialController::class, 'searchApi']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});

require __DIR__ . '/settings.php';
