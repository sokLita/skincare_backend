<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{AdminAuthController, DashboardController, CategoryController, ProductController as AdminProductController, OrderController as AdminOrderController, CustomerController, ReviewController};
use App\Http\Controllers\Api\GoogleAuthController;


Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Google OAuth (requires session middleware)
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register'])->name('register.post');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('/categories', CategoryController::class)->except(['show']);
        Route::resource('/products', AdminProductController::class)->except(['show']);

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        Route::resource('/customers', CustomerController::class)->only(['index', 'show', 'destroy']);
        Route::resource('/reviews', ReviewController::class)->only(['index', 'show', 'destroy']);

        // Inline order status update (AJAX)
        Route::put('/orders/{order}/status-api', [AdminOrderController::class, 'updateStatusApi'])->name('orders.status-api');
    });
});
