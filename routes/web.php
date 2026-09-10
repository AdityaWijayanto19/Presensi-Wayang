<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Admin login
Route::middleware(['guest:user'])->group(function () {
    Route::get('/panel', function () {
        return view('auth.loginadmin');
    })->name('loginadmin');
    Route::post('/prosesloginadmin', [AuthController::class, 'prosesloginadmin']);
});

// Admin panel
Route::middleware(['auth:user', 'role:super_admin|admin|owner,user'])->group(function () {
    require base_path('routes/admin.php');
});

// Karyawan login
Route::middleware(['guest:karyawan'])->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    })->name('login');
    Route::post('/proseslogin', [AuthController::class, 'proseslogin']);
});

// Karyawan panel
Route::middleware(['auth:karyawan'])->group(function () {
    require base_path('routes/karyawan.php');
});
