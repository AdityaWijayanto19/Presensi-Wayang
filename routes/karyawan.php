<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanPresensiController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\RealtimeController;
use Illuminate\Support\Facades\Route;

// Logout
Route::get('/proseslogout', [\App\Http\Controllers\AuthController::class, 'proseslogout']);

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index']);

// Settings
Route::get('/settings', [UserPermissionController::class, 'karyawanSettings']);
Route::get('/api/user/permissions', [UserPermissionController::class, 'getPermissions']);
Route::post('/api/user/permissions/toggle', [UserPermissionController::class, 'togglePermission']);

// Presensi
Route::get('/presensi', [KaryawanPresensiController::class, 'histori']);
Route::get('/presensi/create', [KaryawanPresensiController::class, 'create']);
Route::post('/presensi/store', [KaryawanPresensiController::class, 'store']);
Route::post('/gethistori', [KaryawanPresensiController::class, 'gethistori']);

// Izin
Route::get('/izin', [KaryawanPresensiController::class, 'izin']);
Route::get('/izin/create', [KaryawanPresensiController::class, 'buatizin']);
Route::post('/izin/store', [KaryawanPresensiController::class, 'storeizin']);
Route::delete('/izin/{id}', [KaryawanPresensiController::class, 'deleteizin']);
Route::post('/izin/{id}/approve-atasan', [KaryawanPresensiController::class, 'approveIzinAtasan']);
Route::post('/izin/{id}/reject-atasan', [KaryawanPresensiController::class, 'rejectIzinAtasan']);
Route::get('/presensi/showfileizin/{file}', [KaryawanPresensiController::class, 'showfileizin']);

// Lembur
Route::get('/lembur', [KaryawanPresensiController::class, 'lembur']);
Route::get('/lembur/create', [KaryawanPresensiController::class, 'buatlembur']);
Route::post('/lembur/store', [KaryawanPresensiController::class, 'storelembur']);
Route::delete('/lembur/{id}', [KaryawanPresensiController::class, 'deletelembur']);
Route::get('/lembur/{id}/foto', [KaryawanPresensiController::class, 'fotoLembur']);
Route::post('/lembur/{id}/foto', [KaryawanPresensiController::class, 'storeFotoLembur']);
Route::get('/lembur/{id}/laporan', [KaryawanPresensiController::class, 'buatLaporanLembur']);
Route::post('/lembur/{id}/laporan', [KaryawanPresensiController::class, 'storeLaporanLembur']);
Route::post('/lembur/{id}/approve-atasan', [KaryawanPresensiController::class, 'approveLemburAtasan']);
Route::post('/lembur/{id}/reject-atasan', [KaryawanPresensiController::class, 'rejectLemburAtasan']);
Route::post('/lembur/{id}/approve-laporan-atasan', [KaryawanPresensiController::class, 'approveLaporanLemburAtasan']);
Route::post('/lembur/{id}/reject-laporan-atasan', [KaryawanPresensiController::class, 'rejectLaporanLemburAtasan']);
Route::get('/presensi/showfilelembur/{file}', [KaryawanPresensiController::class, 'showfilelembur']);

// WFH
Route::get('/wfh', [KaryawanPresensiController::class, 'wfh']);
Route::get('/wfh/create', [KaryawanPresensiController::class, 'buatwfh']);
Route::post('/wfh/store', [KaryawanPresensiController::class, 'storewfh']);
Route::delete('/wfh/{id}', [KaryawanPresensiController::class, 'deletewfh']);
Route::get('/wfh/{id}/laporan', [KaryawanPresensiController::class, 'buatLaporanWfh']);
Route::post('/wfh/{id}/laporan', [KaryawanPresensiController::class, 'storeLaporanWfh']);
Route::post('/wfh/{id}/approve-atasan', [KaryawanPresensiController::class, 'approveWfhAtasan']);
Route::post('/wfh/{id}/reject-atasan', [KaryawanPresensiController::class, 'rejectWfhAtasan']);
Route::post('/wfh/{id}/approve-laporan-atasan', [KaryawanPresensiController::class, 'approveLaporanAtasan']);
Route::post('/wfh/{id}/reject-laporan-atasan', [KaryawanPresensiController::class, 'rejectLaporanAtasan']);
Route::get('/presensi/showfilewfh/{file}', [KaryawanPresensiController::class, 'showfilewfh']);

// Notifications
Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
Route::post('/notifications/create', [NotificationController::class, 'store']);

// Push subscription
Route::post('/api/push/subscribe', [PushController::class, 'subscribe']);
Route::post('/api/push/unsubscribe', [PushController::class, 'unsubscribe']);

// Realtime
Route::get('/api/realtime/dashboard', [RealtimeController::class, 'dashboard']);
