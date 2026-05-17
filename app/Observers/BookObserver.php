<?php

namespace App\Observers;

use App\Models\Book;
use App\Services\BookCacheService;

class BookObserver
{
    public function __construct(private BookCacheService $cache) {}

    /**
     * Fires after a book is created or updated.
     * Clears ISBN cache and category cache for the affected book.
     */
    public function saved(Book $book): void
    {
        $this->cache->forgetIsbn($book->isbn);
        $this->cache->invalidateCatalog();
        $this->cache->invalidateCategory($book->category_id);

        // If category changed, also invalidate the old category
        if ($book->wasChanged('category_id')) {
            $this->cache->invalidateCategory($book->getOriginal('category_id'));
        }
    }

    /**
     * Fires after a book is deleted.
     */
    public function deleted(Book $book): void
    {
        $this->cache->forgetIsbn($book->isbn);
        $this->cache->invalidateCatalog();
        $this->cache->invalidateCategory($book->category_id);
    }

    /**
     * Fires after a soft-deleted book is restored.
     */
    public function restored(Book $book): void
    {
        $this->cache->invalidateCatalog();
        $this->cache->invalidateCategory($book->category_id);
    }
}