<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────────────────────────────
// 2FA Challenge  (outside auth — user is not fully logged in yet)
// Throttled: 10 attempts / minute to prevent OTP brute-forcing
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resendOtp'])->name('two-factor.resend');
});

// ──────────────────────────────────────────────────────────────────────────────
// Public routes  (no auth required)
// ──────────────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// Book browsing — redirect.books.index sends admins to admin.books.index
Route::middleware('redirect.books.index')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
});

// ──────────────────────────────────────────────────────────────────────────────
// Authenticated customer routes
//   auth          — must be logged in
//   verified      — email must be verified (EnsureEmailIsVerified bypasses
//                   profile, 2FA and verification pages automatically)
//   redirect.role — redirects admins away from customer-only pages
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'redirect.role'])->group(function () {

    // Customer dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Two-Factor setup (bypassed by EnsureEmailIsVerified) ──────────────────
    Route::get('/profile/two-factor', [TwoFactorController::class, 'show'])->name('profile.two-factor');
    Route::get('/profile/two-factor/totp/setup', [TwoFactorController::class, 'setupTotp'])->name('two-factor.totp.setup');
    Route::post('/profile/two-factor/totp/confirm', [TwoFactorController::class, 'confirmTotp'])->name('two-factor.totp.confirm');
    Route::post('/profile/two-factor/email/enable', [TwoFactorController::class, 'enableEmail'])->name('two-factor.email.enable');
    Route::post('/profile/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('/profile/two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('profile.two-factor.recovery-codes');

    // ── Reviews — throttle to 5 submissions / minute (spam prevention) ────────
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    });
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // ── Orders ────────────────────────────────────────────────────────────────
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/cancel/{order}', [OrderController::class, 'changeStatus'])->name('order.cancel');

    // ── Cart ──────────────────────────────────────────────────────────────────
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
});

// ──────────────────────────────────────────────────────────────────────────────
// Admin-only routes
//   auth  — must be logged in
//   admin — AdminMiddleware: isAdmin() check, 403 for everyone else
//
//   Email-verified gate intentionally omitted for admins (trusted accounts).
//   Mutating endpoints throttled at 30 req/minute to prevent bulk abuse.
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── Category management ───────────────────────────────────────────────────
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // ── Book management ───────────────────────────────────────────────────────
    Route::get('/books', [AdminDashboardController::class, 'books'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::get('/books/{book}', [AdminDashboardController::class, 'bookShow'])->name('books.show');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    });

    // ── Order management ──────────────────────────────────────────────────────
    Route::get('/orders/{status}', [AdminDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/detail/{order}', [AdminDashboardController::class, 'orderShow'])->name('orderShow');
    Route::middleware('throttle:30,1')->group(function () {
        Route::patch('/orders/update/{order}', [AdminDashboardController::class, 'orderStatus'])->name('order.status');
    });

    // ── User management ───────────────────────────────────────────────────────
    Route::get('/users/view/{role}', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminDashboardController::class, 'userShow'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminDashboardController::class, 'userEdit'])->name('users.edit');
    Route::middleware('throttle:30,1')->group(function () {
        Route::patch('/users/{user}', [AdminDashboardController::class, 'userUpdate'])->name('users.update');
        Route::delete('/users/{user}', [AdminDashboardController::class, 'userDestroy'])->name('users.destroy');
    });
});

require __DIR__ . '/auth.php';