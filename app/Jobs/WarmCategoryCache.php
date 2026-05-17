<?php

namespace App\Jobs;

use App\Models\Book;
use App\Services\BookCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WarmCategoryCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(private int $categoryId) {}

    /**
     * Pre-loads the top 1,000 books for this category into Redis.
     * Dispatched after seeding or on a schedule for popular categories.
     */
    public function handle(BookCacheService $cache): void
    {
        $cache->rememberCategoryBooks($this->categoryId, function () {
            return Book::select(['id', 'title', 'author', 'price', 'stock_quantity'])
                ->where('category_id', $this->categoryId)
                ->orderByDesc('stock_quantity')
                ->limit(1000)
                ->get();
        });
    }
}