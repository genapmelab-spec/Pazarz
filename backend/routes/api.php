<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DisputeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Customer-facing REST API (/api/v1/*)
*/

Route::prefix('v1')->group(function () {

    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/stores/{slug}', [StoreController::class, 'show']);
    Route::get('/stores/{slug}/products', [StoreController::class, 'products']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);

        // Addresses
        Route::apiResource('addresses', AddressController::class);

        // Cart
        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'toggle']);
        Route::post('/wishlist/{product}/to-cart', [WishlistController::class, 'addToCart']);

        // Checkout
        Route::post('/checkout', [CheckoutController::class, 'store']);
        Route::post('/checkout/payment-callback', [CheckoutController::class, 'paymentCallback']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
        Route::post('/orders/{orderNumber}/complete', [OrderController::class, 'complete']);

        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::get('/reviews', [ReviewController::class, 'index']);

        // Disputes
        Route::post('/disputes', [DisputeController::class, 'store']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Payment callback (webhook - no user auth, uses signature verification)
    Route::post('/payment/webhook', [CheckoutController::class, 'webhook']);
});
