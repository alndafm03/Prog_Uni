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
use App\Http\Controllers\MessagesController;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

//public routes

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('loginAdmin', [AuthController::class, 'login']);

Route::get('apartments', [ApartmentController::class, 'index']);
Route::post('filter', [ApartmentController::class, 'filter']);


//user route

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [UserController::class, 'logout']);

    //profileroute
    Route::prefix('profile')->group(function () {
        Route::get('', [UserController::class, 'getprofile']);
        Route::post('/up', [UserController::class, 'updateprofile']);
        Route::post('', [UserController::class, 'deletemyaccount']);
    });

    //apartmentroute
    Route::prefix('apartments')->group(function () {

        Route::post('', [ApartmentController::class, 'store']);
        Route::get('/Myapartment', [ApartmentController::class, 'myapartments']);
        Route::post('{apartment}', [ApartmentController::class, 'update']);
        Route::delete('{apartment}', [ApartmentController::class, 'destroy']);
    });
    Route::prefix('bookings')->group(function () {
        Route::get('', [BookingController::class, 'renterbooking']);
        Route::post('', [BookingController::class, 'store']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::post('/{id}', [BookingController::class, 'update']);
        Route::delete('/{id}', [BookingController::class, 'destroy']);
    });
    Route::prefix('owner')->group(function () {

        Route::get('', [OwnerController::class, 'ownerbooking']);
        Route::get('/pending', [OwnerController::class, 'ownerbookingpending']);
        Route::post('app/{id}', [OwnerController::class, 'approve']);
        Route::post('rej/{id}', [OwnerController::class, 'reject']);
    });
    Route::prefix('reviews')->group(function () {
        Route::get('', [ReviewController::class, 'index']);
        Route::get('/{booking_id}', [ReviewController::class, 'showapartmentreview']);
        Route::post('/{booking_id}', [ReviewController::class, 'store']);
        Route::post('/up/{id}', [ReviewController::class, 'update']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
    });
    Route::post('favorite/{apartment_id}', [FavoriteController::class, 'toggleFavorite']);
    Route::get('favorite', [FavoriteController::class, 'getfavorites']);
    Route::delete('favorite/{apartment_id}', [FavoriteController::class, 'removefavorite']);

    Route::get('/messages/{booking_id}', [MessagesController::class, 'index']);
    Route::post('/messages', [MessagesController::class, 'store']);
    Route::get('/chats', [MessagesController::class, 'inbox']);
});



Route::get('apartments/{apartment}', [ApartmentController::class, 'show']); // GET /apartments/{id}

Route::middleware(['auth:sanctum', 'CheckUser'])->group(function () {

    Route::post('logoutAdmin', [AuthController::class, 'logout']);

    Route::get('getpending', [UserManagementController::class, 'getpending']);
    Route::post('approveuser/{id}', [UserManagementController::class, 'approveuser']);
    Route::post('rejectuser/{id}', [UserManagementController::class, 'rejectuser']);
    Route::delete('deleteuser/{id}', [UserManagementController::class, 'deleteuser']);
    Route::get('users', [UserManagementController::class, 'getallusers']);
    Route::get('dashboard', [DashboardController::class, 'index']);
});
