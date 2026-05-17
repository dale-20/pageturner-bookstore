<?php

namespace App\Repositories;

use App\Models\Book;
use App\Services\BookCacheService;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Model;

class BookRepository
{
    public function __construct(private BookCacheService $cache) {}

    // ── Public Catalog ────────────────────────────────────────────────────────

    /**
     * Paginated catalog listing for public views.
     * Uses covering index idx_books_catalog_filter.
     */
    public function catalog(?int $categoryId = null, int $perPage = 100): LengthAwarePaginator
    {
        $query = Book::select([
                'id', 'isbn', 'title', 'author',
                'price', 'stock_quantity', 'category_id',
            ])
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('id', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a single book by ISBN.
     * Result is Redis-cached for 1 hour via BookCacheService.
     */
    public function findByIsbn(string $isbn): ?Model
    {
        return $this->cache->rememberIsbn($isbn, function () use ($isbn) {
            return Book::with('category')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('isbn', $isbn)
                ->first();
        });
    }

    // ── Admin Catalog ─────────────────────────────────────────────────────────

    /**
     * Cursor-paginated listing for admin views.
     * Avoids OFFSET slowdown on deep pages of 1M rows.
     */
    public function adminCatalog(
        ?string $search     = null,
        ?int    $categoryId = null,
        ?string $isbn       = null,
        int     $perPage    = 50
    ): CursorPaginator {
        $query = Book::with('category:id,name')
            ->orderBy('id', 'desc');

        if (!empty($isbn)) {
            return $query->where('isbn', $isbn)->cursorPaginate($perPage);
        }

        if (!empty($search)) {
            if (strlen($search) >= 3) {
                $query->whereRaw(
                    "search_vector @@ plainto_tsquery('english', ?)",
                    [$search]
                );
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%");
                });
            }
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->cursorPaginate($perPage);
    }

    // ── Categories ────────────────────────────────────────────────────────────

    /**
     * All categories, Redis-cached for 1 hour.
     */
    public function allCategories()
    {
        return $this->cache->rememberCategories(fn() => Category::all());
    }

    // ── Popular Books per Category ────────────────────────────────────────────

    /**
     * Top 1000 books for a category, cached with category tag.
     * Used by WarmCategoryCache job.
     */
    public function popularByCategory(int $categoryId, int $limit = 1000)
    {
        return $this->cache->rememberCategoryBooks($categoryId, function () use ($categoryId, $limit) {
            return Book::select(['id', 'title', 'author', 'price', 'stock_quantity'])
                ->where('category_id', $categoryId)
                ->orderByDesc('stock_quantity')
                ->limit($limit)
                ->get();
        });
    }
}