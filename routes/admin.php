<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminPresensiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\UnitperusahaanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\RealtimeController;
use Illuminate\Support\Facades\Route;

// Logout
Route::get('/proseslogoutadmin', [\App\Http\Controllers\AuthController::class, 'proseslogoutadmin']);

// Dashboard
Route::get('/panel/dashboard', [DashboardController::class, 'dashboardadmin']);

// Settings
Route::get('/panel/settings', [UserPermissionController::class, 'adminSettings']);
Route::get('/api/admin/permissions', [UserPermissionController::class, 'adminGetPermissions']);
Route::post('/api/admin/permissions/toggle', [UserPermissionController::class, 'adminTogglePermission']);

// User management
Route::group(['middleware' => 'permission:user-manage,user'], function () {
    Route::get('/panel/users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store']);
    Route::post('/users/{id}/resetpassword', [UserController::class, 'resetpassword']);
    Route::post('/users/{id_user}/update', [UserController::class, 'update']);
    Route::post('/users/{id_user}/delete', [UserController::class, 'delete']);
});

// Unit perusahaan
Route::get('/panel/unit', [UnitperusahaanController::class, 'index'])->middleware('permission:unit-view,user');
Route::post('/unitperusahaan/edit', [UnitperusahaanController::class, 'edit'])->middleware('permission:unit-view,user');
Route::group(['middleware' => 'permission:unit-create,user'], function () {
    Route::post('/unitperusahaan/store', [UnitperusahaanController::class, 'store']);
});
Route::group(['middleware' => 'permission:unit-edit,user'], function () {
    Route::post('/unitperusahaan/{id}/update', [UnitperusahaanController::class, 'update']);
});
Route::group(['middleware' => 'permission:unit-delete,user'], function () {
    Route::post('/unitperusahaan/{id}/delete', [UnitperusahaanController::class, 'delete']);
});

// Karyawan
Route::get('/panel/karyawan', [KaryawanController::class, 'index'])->middleware('permission:karyawan-view,user');
Route::get('/karyawan/get-atasan', [KaryawanController::class, 'getAtasan'])->middleware('permission:karyawan-view,user');
Route::post('/karyawan/edit', [KaryawanController::class, 'edit'])->middleware('permission:karyawan-view,user');
Route::group(['middleware' => 'permission:karyawan-create,user'], function () {
    Route::post('/karyawan/store', [KaryawanController::class, 'store']);
});
Route::group(['middleware' => 'permission:karyawan-edit,user'], function () {
    Route::post('/karyawan/{nik}/update', [KaryawanController::class, 'update']);
});
Route::group(['middleware' => 'permission:karyawan-delete,user'], function () {
    Route::post('/karyawan/{nik}/delete', [KaryawanController::class, 'delete']);
});

// Monitoring
Route::get('/panel/monitoring', [AdminPresensiController::class, 'monitoring'])->middleware('permission:monitoring-view,user');
Route::post('/getpresensi', [AdminPresensiController::class, 'getpresensi'])->middleware('permission:monitoring-view,user');
Route::post('/tampilkanpetamasuk', [AdminPresensiController::class, 'tampilkanpetamasuk'])->middleware('permission:monitoring-view,user');
Route::post('/tampilkanpetapulang', [AdminPresensiController::class, 'tampilkanpetapulang'])->middleware('permission:monitoring-view,user');

// Laporan
Route::get('/panel/laporan', [AdminPresensiController::class, 'laporan'])->middleware('permission:laporan-view,user');
Route::post('/getkaryawanbyunit', [AdminPresensiController::class, 'getkaryawanbyunit'])->middleware('permission:laporan-view,user');
Route::post('/presensi/cetaklaporan', [AdminPresensiController::class, 'cetaklaporan'])->middleware('permission:laporan-view,user');

// Data izin
Route::get('/panel/izin', [AdminPresensiController::class, 'dataizin'])->middleware('permission:izin-view,user');
Route::group(['middleware' => 'permission:izin-delete,user'], function () {
    Route::post('/presensi/dataizin/{id}/delete', [AdminPresensiController::class, 'deleteizinadmin']);
});

// Data lembur
Route::get('/panel/lembur', [AdminPresensiController::class, 'datalembur'])->middleware('permission:lembur-view,user');
Route::group(['middleware' => 'permission:lembur-delete,user'], function () {
    Route::post('/presensi/datalembur/{id}/delete', [AdminPresensiController::class, 'deletelemburadmin']);
});

// Data WFH
Route::get('/panel/wfh', [AdminPresensiController::class, 'datawfh'])->middleware('permission:wfh-view,user');
Route::group(['middleware' => 'permission:wfh-delete,user'], function () {
    Route::post('/presensi/datawfh/{id}/delete', [AdminPresensiController::class, 'deletewfhadmin']);
});
Route::group(['middleware' => 'permission:wfh-approve,user'], function () {
    Route::post('/presensi/datawfh/{id}/approve', [AdminPresensiController::class, 'approveWfhAdmin']);
    Route::post('/presensi/datawfh/{id}/reject', [AdminPresensiController::class, 'rejectWfhAdmin']);
    Route::post('/presensi/datawfh/{id}/approve-laporan-admin', [AdminPresensiController::class, 'approveLaporanAdmin']);
    Route::post('/presensi/datawfh/{id}/reject-laporan-admin', [AdminPresensiController::class, 'rejectLaporanAdmin']);
});

// Edit data
Route::group(['middleware' => 'permission:presensi-edit,user'], function () {
    Route::post('/presensi/izin/{id}/edit', [AdminPresensiController::class, 'editIzinAdmin']);
    Route::post('/presensi/izin/{id}/update', [AdminPresensiController::class, 'updateIzinAdmin']);
    Route::post('/presensi/lembur/{id}/edit', [AdminPresensiController::class, 'editLemburAdmin']);
    Route::post('/presensi/lembur/{id}/update', [AdminPresensiController::class, 'updateLemburAdmin']);
    Route::post('/presensi/wfh/{id}/edit', [AdminPresensiController::class, 'editWfhAdmin']);
    Route::post('/presensi/wfh/{id}/update', [AdminPresensiController::class, 'updateWfhAdmin']);
    Route::post('/presensi/{id}/edit', [AdminPresensiController::class, 'editPresensiAdmin']);
    Route::post('/presensi/{id}/update', [AdminPresensiController::class, 'updatePresensiAdmin']);
});

// Realtime API
Route::get('/api/realtime/admin', [RealtimeController::class, 'admin']);
Route::get('/api/realtime/admin/wfh-check', [RealtimeController::class, 'adminWfhCheck']);
Route::get('/api/realtime/admin/wfh-data', [RealtimeController::class, 'adminWfhData']);
