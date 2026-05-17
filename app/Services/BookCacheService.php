<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class BookCacheService
{
    // Cache TTLs in seconds
    private const TTL_ISBN     = 3600; // 1 hour
    private const TTL_CATALOG  = 300;  // 5 minutes
    private const TTL_CATEGORY = 3600; // 1 hour

    // ── ISBN ──────────────────────────────────────────────────────────────────

    public function rememberIsbn(string $isbn, callable $callback): mixed
    {
        return Cache::remember("book:isbn:{$isbn}", self::TTL_ISBN, $callback);
    }

    public function forgetIsbn(string $isbn): void
    {
        Cache::forget("book:isbn:{$isbn}");
    }

    // ── Catalog ───────────────────────────────────────────────────────────────

    public function invalidateCatalog(): void
    {
        Cache::forget('books:catalog');
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function rememberCategories(callable $callback): mixed
    {
        return Cache::remember('categories', self::TTL_CATEGORY, $callback);
    }

    public function invalidateCategory(int $categoryId): void
    {
        try {
            Cache::tags(["category:{$categoryId}"])->flush();
        } catch (\BadMethodCallException) {
            // Tags not supported on this driver — skip silently
        }
    }

    public function rememberCategoryBooks(int $categoryId, callable $callback): mixed
    {
        try {
            return Cache::tags(["category:{$categoryId}"])
                ->remember("category:{$categoryId}:popular", self::TTL_CATALOG, $callback);
        } catch (\BadMethodCallException) {
            return $callback();
        }
    }
}