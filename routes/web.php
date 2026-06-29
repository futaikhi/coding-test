<?php

use App\Http\Controllers\RoleController;
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
});

require __DIR__ . '/settings.php';
