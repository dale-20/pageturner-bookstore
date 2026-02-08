<?php
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
// Book browsing (public)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
// Category browsing (public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');


// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Review routes
    Route::post('/books/{book}/reviews', [
        ReviewController::class,
        'store'
    ])->name('reviews.store');
    Route::delete('/reviews/{review}', [
        ReviewController::class,
        'destroy'
    ])->name('reviews.destroy');
    // Order routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});


// Admin-only routes (Category & Book management)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Category management
    Route::get('/categories/create', [
        CategoryController::class,
        'create'
    ])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get(
        '/categories/{category}/edit',
        [CategoryController::class, 'edit']
    )->name('categories.edit');
    Route::put('/categories/{category}', [
        CategoryController::class,
        'update'
    ])->name('categories.update');
    Route::delete('/categories/{category}', [
        CategoryController::class,
        'destroy'
    ])->name('categories.destroy');
    // Book management
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});
require __DIR__ . '/auth.php';