<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController; 
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController; // Add this line
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderConfirmationMail;

// Public routes
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Public products route
Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');

// Public Reviews Route
Route::get('/reviews', [ReviewController::class, 'index'])->name('shop.reviews');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard route with role-based redirect
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return view('dashboard');
        }
        return redirect()->route('customer.dashboard');
    })->name('dashboard');

    // Products route accessible to all authenticated users
    // Route::get('/products', [ProductController::class, 'index'])
    //     ->name('products.index');

    // Customer routes - no admin middleware
    Route::get('/customer/dashboard', [CustomerController::class, 'dashboard'])
        ->name('customer.dashboard');
    Route::get('/cart/checkout', [CartController::class, 'checkout'])
        ->name('cart.checkout');
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');
    Route::post('/cart/add/{product}', [CartController::class, 'addItem'])
        ->name('cart.add');
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])
        ->name('cart.remove');
    Route::post('/cart/update/{product}', [CartController::class, 'updateQuantity'])->name('cart.update');
    Route::get('/customer/orders/{order}', [OrderController::class, 'customerShow'])->name('customer.orders.show');

    // Review Routes
    Route::get('/reviews/create/{order}', [ReviewController::class, 'redirectToProduct'])
        ->name('reviews.create')
        ->middleware(['auth']);
    Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])
        ->name('product.reviews.store');
    Route::put('/product/{product}/reviews/{review}', [ReviewController::class, 'update'])
        ->name('product.reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // Admin routes - consolidate all admin routes under one middleware group
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        // Categories Resource
        Route::resource('categories', \App\Http\Controllers\CategoryController::class);

        // Products Resource (place FIRST in group)
        Route::resource('products', ProductController::class)->except(['index', 'show']);

        // Product Import Routes
        Route::get('/products/import', [ProductImportController::class, 'showImportForm'])
             ->name('products.import.form');
        Route::post('/products/import', [ProductImportController::class, 'import'])
             ->name('products.import');
       
        // Products Management (individual routes)
        Route::get('/products/{product}', [ProductController::class, 'show'])
            ->name('products.show');
        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('products.create');
        Route::put('/products/{product}/restore', [ProductController::class, 'restore'])
            ->name('products.restore');

        // Orders Management
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}/manage', [OrderController::class, 'manage'])->name('orders.manage');
        Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel'])
            ->name('orders.cancel');
        Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
        
        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::resource('users', UserController::class)->except(['index']);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        Route::post('/users/{user}/toggle-role', [UserController::class, 'toggleRole'])
            ->name('users.toggle-role');
        
        // Orders Resource
        Route::resource('orders', OrderController::class)->except(['store', 'index', 'manage', 'update-status']);
        
        // Admin management
        Route::get('/admin/manage', [AdminController::class, 'manage'])->name('admin.manage');

        // Reviews Routes
        Route::post('/reviews/{order}', [ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::get('/reviews/data', [ReviewController::class, 'getReviews'])->name('reviews.get');

        // Reviews Management (Admin only)
        Route::get('/admin/reviews', [ReviewController::class, 'adminIndex'])->name('admin.reviews.index');
        Route::get('/admin/reviews/data', [ReviewController::class, 'adminData'])->name('admin.reviews.data');
        Route::delete('/admin/reviews/{review}', [ReviewController::class, 'adminDestroy'])->name('admin.reviews.destroy');
    });

    // Public/Customer Review Routes
    Route::get('/product/{product}/reviews', [ReviewController::class, 'index'])->name('product.reviews');
    Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->name('product.reviews.store');
    Route::put('/product/{product}/reviews/{review}', [ReviewController::class, 'update'])->name('product.reviews.update');
});

// Routes for order status update by customers
Route::middleware(['auth'])->group(function () {
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
});

// Admin routes - consolidate all admin routes under one middleware group
// Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
//     // Remove the duplicate categories resource route and keep it only here
//     // Route::resource('categories', \App\Http\Controllers\CategoryController::class);
// });

Route::middleware(['auth'])->group(function () {
    Route::resource('categories', CategoryController::class);
    
    // Customer Order History Route
    Route::get('/orders/history', [App\Http\Controllers\OrderController::class, 'customerHistory'])
        ->name('order-history.index');
    Route::get('/orders/history', [App\Http\Controllers\OrderController::class, 'history'])
        ->name('orders.history');
    
    // Customer Order Delivery Confirmation Route
    Route::put('/orders/{order}/confirm-delivery', [App\Http\Controllers\OrderController::class, 'confirmDelivery'])
        ->name('orders.confirm-delivery');
});

// Additional routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';