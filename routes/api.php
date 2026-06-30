<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, ProductController, CategoryController, WishlistController, CartController, OrderController, ReviewController, AdminController, ChatController, TelegramBotController, ConsultationController};

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/admin/register', [AuthController::class, 'adminRegister']);
Route::post('/admin/login',    [AuthController::class, 'adminLogin']);




Route::get('/categories',        [CategoryController::class, 'index']);
Route::post('/categories',       [CategoryController::class, 'store']);
Route::get('/categories/{id}',   [CategoryController::class, 'show']);
Route::put('/categories/{id}',   [CategoryController::class, 'update']);
Route::delete('/categories/{id}',[CategoryController::class, 'destroy']);

Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

    // Chat routes (customer)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/chat/messages', [ChatController::class, 'myMessages']);
        Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
        Route::get('/chat/unread', [ChatController::class, 'unreadCount']);
        Route::post('/chat/mark-read', [ChatController::class, 'markCustomerRead']);
    });

    // Customer-only routes
    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/photo', [AuthController::class, 'updatePhoto']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/cart',        [CartController::class, 'index']);
    Route::post('/cart',       [CartController::class, 'store']);
    Route::put('/cart/{id}',   [CartController::class, 'update']);
    Route::delete('/cart/{id}',[CartController::class, 'destroy']);

    Route::get('/wishlist',            [WishlistController::class, 'index']);
    Route::post('/wishlist',           [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}',    [WishlistController::class, 'destroy']);

    Route::get('/orders',        [OrderController::class, 'index']);
    Route::get('/orders/{id}',   [OrderController::class, 'show']);
    Route::post('/checkout',     [OrderController::class, 'checkout']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Consultation routes (customer)
    Route::get('/consultation', [ConsultationController::class, 'getOrCreateConsultation']);
    Route::post('/consultation/message', [ConsultationController::class, 'sendMessage']);
    Route::get('/consultations', [ConsultationController::class, 'myConsultations']);
});

// Admin consultation routes
Route::middleware(['auth:sanctum', 'admin.api'])->group(function () {
    Route::get('/admin/consultations', [ConsultationController::class, 'adminConsultations']);
    Route::get('/admin/consultations/{id}', [ConsultationController::class, 'adminConsultationDetail']);
    Route::post('/admin/consultations/{id}/reply', [ConsultationController::class, 'adminReply']);
    Route::post('/admin/consultations/{id}/complete', [ConsultationController::class, 'completeConsultation']);
    Route::get('/admin/consultations/unread/count', [ConsultationController::class, 'unreadSellerRequests']);
});

// Admin chat routes
Route::middleware(['auth:sanctum', 'admin.api'])->group(function () {
    Route::get('/admin/chat/conversations', [ChatController::class, 'conversations']);
    Route::get('/admin/chat/conversations/{userId}', [ChatController::class, 'conversationMessages']);
    Route::post('/admin/chat/reply/{userId}', [ChatController::class, 'replyMessage']);
    Route::post('/admin/chat/mark-read/{userId}', [ChatController::class, 'markAsRead']);
});

// Telegram bot webhook (no auth - Telegram calls this)
Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);

// Telegram bot admin routes
Route::middleware(['auth:sanctum', 'admin.api'])->group(function () {
    Route::post('/telegram/set-webhook', [TelegramBotController::class, 'setWebhook']);
    Route::post('/telegram/remove-webhook', [TelegramBotController::class, 'removeWebhook']);
    Route::get('/telegram/webhook-info', [TelegramBotController::class, 'webhookInfo']);
    Route::post('/telegram/send-message', [TelegramBotController::class, 'sendMessage']);
});

    // Admin-only routes
Route::middleware(['auth:sanctum', 'admin.api'])->group(function () {
    Route::post('/products',         [ProductController::class, 'store']);
    Route::put('/products/{id}',     [ProductController::class, 'update']);
    Route::delete('/products/{id}',  [ProductController::class, 'destroy']);

    // Admin dashboard & orders
    Route::get('/admin/dashboard',      [AdminController::class, 'dashboard']);
    Route::get('/admin/new-orders-count', [AdminController::class, 'newOrdersCount']);
    Route::get('/admin/orders',         [AdminController::class, 'orders']);
    Route::get('/admin/orders/{id}',    [AdminController::class, 'orderDetail']);
});
