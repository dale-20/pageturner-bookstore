<?php

use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\ReviewSummaryController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle.tiered:public')->group(function () {
    Route::get('/books', [BookApiController::class, 'index'])->name('api.books.index');
    Route::get('/books/{book}', [BookApiController::class, 'show'])->name('api.books.show');
    Route::get('/books/{bookId}/ai-summary', [ReviewSummaryController::class, 'show'])->name('api.ai.summary');
    Route::post('/books/{bookId}/ai-summary/generate', [ReviewSummaryController::class, 'generateNow'])->name('api.ai.summary.generate');
});
