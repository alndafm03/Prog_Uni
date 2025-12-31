<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\FavoriteController;
//public routes

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
// Route::post('loginAdmin', [AuthController::class, 'login']);

//user routes

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [UserController::class, 'logout']);

    //profile routes
    Route::prefix('profile')->group(function () {
        Route::get('', [UserController::class, 'getprofile']);
        Route::put('', [UserController::class, 'updateprofile']);
        Route::post('', [UserController::class, 'deleteAccount']);
    });

    //apartment routes
    Route::prefix('apartments')->group(function () {

        Route::get('', [ApartmentController::class, 'index']); // GET /apartments
        Route::post('', [ApartmentController::class, 'store']); // POST /apartments

        Route::post('filter', [ApartmentController::class, 'filter']); // GET /apartments/filter/list
        Route::get('Myapartment', [ApartmentController::class, 'myApartments']); // GET /apartments/owner/list

        Route::get('{apartment}', [ApartmentController::class, 'show']); // GET /apartments/{id}
        Route::put('{apartment}', [ApartmentController::class, 'update']); // PUT /apartments/{id}
        Route::delete('{apartment}', [ApartmentController::class, 'destroy']); // DELETE /apartments/{id}

    });
    Route::prefix('bookings')->group(function () {
        //عرض حجوزات المستأجر
        Route::get('', [BookingController::class, 'renterBooking']);
        // إنشاء حجز جديد
        Route::post('', [BookingController::class, 'store']);
        // عرض حجز واحد (اختياري إذا أردت)
        Route::get('/{id}', [BookingController::class, 'show']);
        // تعديل حجز
        Route::put('/{id}', [BookingController::class, 'update']);
        // حذف حجز
        Route::delete('/{id}', [BookingController::class, 'destroy']);
    });
    Route::prefix('owner')->group(function () {
        //   عرض كل الحجوزات (للمشرف أو المالك)
        Route::get('', [OwnerController::class, 'ownerBooking']);
        Route::get('/pending', [OwnerController::class, 'ownerbookingpending']);
        Route::post('app/{id}', [OwnerController::class, 'approve']);
        Route::post('rej/{id}', [OwnerController::class, 'reject']);
    });
    Route::prefix('reviews')->group(function () {
        // عرض كل المراجعات
        Route::get('', [ReviewController::class, 'index']);
        // عرض مراجعات مرتبطة بحجز معين
        Route::get('/{booking_id}', [ReviewController::class, 'showByBooking']);
        // إنشاء مراجعة جديدة
        Route::post('/{booking_id}', [ReviewController::class, 'store']);
        // تعديل مراجعة
        Route::put('/{id}', [ReviewController::class, 'update']);
        // حذف مراجعة
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
    });
    Route::post('favorite/{apartment_id}', [FavoriteController::class, 'toggleFavorite']);
    Route::get('favorite', [FavoriteController::class, 'getFavorites']);
});


//admin routes

Route::prefix('admin')->group(function () {

    // صفحة تسجيل الدخول
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');

    // تنفيذ تسجيل الدخول
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
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


// Route::middleware(['auth:sanctum', 'CheckUser'])->group(function () {

//     Route::post('logoutAdmin', [AuthController::class, 'logout']);

//     Route::get('getpending', [UserManagementController::class, 'getpending']);
//     Route::post('approveuser/{id}', [UserManagementController::class, 'approveuser']);
//     Route::post('rejectuser/{id}', [UserManagementController::class, 'rejectuser']);
//     Route::post('deleteuser/{id}', [UserManagementController::class, 'deleteuser']);
//     Route::get('dashboard', [DashboardController::class, 'index']);
// });
