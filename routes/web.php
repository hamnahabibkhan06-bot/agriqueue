<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\InspectorController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StaffController;
use App\Http\Middleware\RoleMiddleware as Role;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/board/{center}', [PublicController::class, 'board'])->name('board');
Route::get('/board/{center}/data', [PublicController::class, 'boardData'])->name('board.data');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->dashboardRoute()))->name('dashboard');

    // Farmer
    Route::middleware(Role::class . ':farmer')->prefix('farmer')->name('farmer.')->group(function () {
        Route::get('/', [FarmerController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [FarmerController::class, 'index'])->name('bookings');
        Route::get('/bookings/new', [FarmerController::class, 'create'])->name('bookings.create');
        Route::get('/slots', [FarmerController::class, 'slotTable'])->name('slots');
        Route::post('/bookings', [FarmerController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [FarmerController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/status', [FarmerController::class, 'status'])->name('bookings.status');
        Route::post('/bookings/{booking}/cancel', [FarmerController::class, 'cancel'])->name('bookings.cancel');
        Route::get('/history', [FarmerController::class, 'history'])->name('history');
        Route::get('/notifications', [FarmerController::class, 'notices'])->name('notices');
    });

    // Receipts: farmer owner, center staff or admin (checked in controller)
    Route::get('/receipt/{booking}', [StaffController::class, 'receipt'])->name('receipt');

    // Procurement center staff (admin may supervise any center)
    Route::middleware(Role::class . ':staff,admin')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/queue', [StaffController::class, 'queue'])->name('queue');
        Route::post('/bookings/{booking}/advance', [StaffController::class, 'advance'])->name('advance');
        Route::post('/bookings/{booking}/weigh', [StaffController::class, 'weigh'])->name('weigh');
        Route::post('/bookings/{booking}/flag', [StaffController::class, 'flag'])->name('flag');
    });

    // Quality inspector
    Route::middleware(Role::class . ':inspector,admin')->prefix('inspector')->name('inspector.')->group(function () {
        Route::get('/', [InspectorController::class, 'index'])->name('index');
        Route::post('/bookings/{booking}', [InspectorController::class, 'store'])->name('store');
    });

    // Administrator / management
    Route::middleware(Role::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/centers', [AdminController::class, 'centers'])->name('centers');
        Route::post('/centers', [AdminController::class, 'centerStore'])->name('centers.store');
        Route::put('/centers/{center}', [AdminController::class, 'centerUpdate'])->name('centers.update');
        Route::get('/crops', [AdminController::class, 'crops'])->name('crops');
        Route::post('/crops', [AdminController::class, 'cropStore'])->name('crops.store');
        Route::put('/crops/{crop}', [AdminController::class, 'cropUpdate'])->name('crops.update');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
        Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    });
});
