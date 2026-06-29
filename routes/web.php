<?php

use App\Http\Controllers\CategoryController;
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
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'totalUsers' => User::count(),
            'totalRoles' => Role::count(),
            'totalMaterials' => 0, // Sementara 0, nanti di Topik 2 kita hitung dari tabel Material
        ]);
    })->name('dashboard');

    // CRUD Role Management - Bisa diakses semua role setelah login
    Route::resource('roles', RoleController::class);

    // CRUD User Account - Hanya untuk Administrator
    Route::middleware(['role:Administrator'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // CRUD Material
    Route::resource('materials', MaterialController::class);
    Route::post('/materials/import/upload', [MaterialController::class, 'importUpload']);
    Route::post('/materials/import/process', [MaterialController::class, 'importProcess']);
    Route::get('/materials/import/progress', [MaterialController::class, 'importProgress']);

    // CRUD Category
    Route::resource('categories', CategoryController::class);

    // CRUD Stock Mutation
    Route::resource('stock-mutations', StockMutationController::class);

    // Endpoint API untuk remote search Select2 (Mengembalikan format { value, label })
    Route::get('/api/categories-search', [CategoryController::class, 'searchApi']);
    Route::get('/api/materials-search', [MaterialController::class, 'searchApi']);

    Route::post('/materials/export', [MaterialController::class, 'triggerExport']);
    Route::get('/materials/export/check', [MaterialController::class, 'checkExportStatus']);
});

require __DIR__ . '/settings.php';
