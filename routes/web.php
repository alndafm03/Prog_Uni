<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
Route::prefix('admin')->middleware(['auth:sanctum', 'CheckUser'])->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // إدارة المستخدمين
    Route::get('getpending', [UserManagementController::class, 'getpending'])->name('admin.users.pending');
    Route::post('approveuser/{id}', [UserManagementController::class, 'approveuser'])->name('admin.users.approve');
    Route::post('rejectuser/{id}', [UserManagementController::class, 'rejectuser'])->name('admin.users.reject');
    Route::delete('deleteuser/{id}', [UserManagementController::class, 'deleteuser'])->name('admin.users.delete');

    // تسجيل الخروج
    Route::post('logoutAdmin', [AuthController::class, 'logout'])->name('admin.logout');
});
