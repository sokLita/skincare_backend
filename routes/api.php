<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, ProductController, CategoryController, WishlistController, CartController, OrderController, ReviewController};

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/categories',        [CategoryController::class, 'index']);
Route::post('/categories',       [CategoryController::class, 'store']);
Route::get('/categories/{id}',   [CategoryController::class, 'show']);
Route::put('/categories/{id}',   [CategoryController::class, 'update']);
Route::delete('/categories/{id}',[CategoryController::class, 'destroy']);

Route::post('/products',         [ProductController::class, 'store']);
Route::put('/products/{id}',     [ProductController::class, 'update']);
Route::delete('/products/{id}',  [ProductController::class, 'destroy']);

Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{id}',     [ProductController::class, 'show']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',              [AuthController::class, 'logout']);
    Route::get('/profile',              [AuthController::class, 'profile']);
    Route::put('/profile',              [AuthController::class, 'updateProfile']);
    Route::put('/change-password',      [AuthController::class, 'changePassword']);

    Route::get('/wishlist',             [WishlistController::class, 'index']);
    Route::post('/wishlist',            [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}',     [WishlistController::class, 'destroy']);

    Route::get('/cart',                 [CartController::class, 'index']);
    Route::post('/cart',                [CartController::class, 'store']);
    Route::put('/cart/{id}',            [CartController::class, 'update']);
    Route::delete('/cart/{id}',         [CartController::class, 'destroy']);

    Route::post('/checkout',            [OrderController::class, 'checkout']);
    Route::get('/orders',               [OrderController::class, 'index']);
    Route::get('/orders/{id}',          [OrderController::class, 'show']);

    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);
});
