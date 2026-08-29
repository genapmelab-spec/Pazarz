<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Web\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Web\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Web\Seller\InventoryController as SellerInventoryController;
use App\Http\Controllers\Web\Seller\StoreController as SellerStoreController;
use App\Http\Controllers\Web\Seller\PromotionController as SellerPromotionController;
use App\Http\Controllers\Web\Seller\ReviewController as SellerReviewController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\Web\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Web\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Web\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Web\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Web\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Web\Admin\SettingController as AdminSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Seller Dashboard (/seller/*) and Admin Dashboard (/admin/*)
*/

/*
|--------------------------------------------------------------------------
| Home Route — redirect to dashboard login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

// Admin/Seller Auth Routes (Session-based, Blade)
Route::middleware('guest')->prefix('dashboard')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register/seller', [RegisterController::class, 'showSellerRegistration'])->name('seller.register');
    Route::post('/register/seller', [RegisterController::class, 'registerSeller']);
});

Route::post('/dashboard/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');

/*
|--------------------------------------------------------------------------
| Seller Dashboard Routes (/seller/*)
|--------------------------------------------------------------------------
*/
Route::prefix('seller')->name('seller.')->middleware(['auth', 'seller', 'verified'])->group(function () {
    Route::get('/', [SellerDashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');

    // Inventory
    Route::get('/inventory', [SellerInventoryController::class, 'index'])->name('inventory.index');
    Route::patch('/inventory/{variant}', [SellerInventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/bulk-update', [SellerInventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');

    // Orders
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{subOrder}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{subOrder}/confirm', [SellerOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{subOrder}/ship', [SellerOrderController::class, 'ship'])->name('orders.ship');
    Route::patch('/orders/{subOrder}/cancel', [SellerOrderController::class, 'cancel'])->name('orders.cancel');

    // Store Settings
    Route::get('/settings', [SellerStoreController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SellerStoreController::class, 'update'])->name('settings.update');

    // Promotions
    Route::get('/promotions', [SellerPromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/create', [SellerPromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions', [SellerPromotionController::class, 'store'])->name('promotions.store');

    // Reviews
    Route::get('/reviews', [SellerReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [SellerReviewController::class, 'reply'])->name('reviews.reply');
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes (/admin/*)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'verified'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::patch('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');

    // Sellers
    Route::get('/sellers', [AdminSellerController::class, 'index'])->name('sellers.index');
    Route::get('/sellers/{seller}', [AdminSellerController::class, 'show'])->name('sellers.show');
    Route::patch('/sellers/{seller}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('/sellers/{seller}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject');

    // Products (Moderation)
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [AdminProductController::class, 'show'])->name('products.show');
    Route::patch('/products/{product}/deactivate', [AdminProductController::class, 'deactivate'])->name('products.deactivate');
    Route::patch('/products/{product}/activate', [AdminProductController::class, 'activate'])->name('products.activate');

    // Categories
    Route::resource('categories', AdminCategoryController::class)->except(['show']);

    // Orders (Monitoring)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

    // Disputes
    Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
    Route::patch('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
    Route::patch('/disputes/{dispute}/reject', [AdminDisputeController::class, 'reject'])->name('disputes.reject');
    Route::post('/disputes/{dispute}/messages', [AdminDisputeController::class, 'sendMessage'])->name('disputes.message');

    // Audit Logs
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

    // Platform Settings
    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});


