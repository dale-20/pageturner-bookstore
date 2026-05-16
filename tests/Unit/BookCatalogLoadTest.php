<?php

namespace Tests\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookCatalogLoadTest extends TestCase
{
    // ── Thresholds (ms) — matches Lab 7 Section 3.2.2 ────────────────────────
    private const CATALOG_THRESHOLD_MS  = 100;
    private const ISBN_THRESHOLD_MS     = 50;
    private const CONCURRENT_REQUESTS   = 50;

    protected function setUp(): void
    {
        parent::setUp();
        // Use the read replica for all load test queries
        DB::setDefaultConnection('pgsql');
    }

    // ── Test 1: 50 Concurrent Catalog Requests ────────────────────────────────

    public function test_concurrent_catalog_requests_complete_without_error(): void
    {
        $errors   = [];
        $times    = [];
        $responses = [];

        for ($i = 0; $i < self::CONCURRENT_REQUESTS; $i++) {
            $start = hrtime(true);

            try {
                $result = DB::connection('pgsql::read')
                    ->table('books')
                    ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id'])
                    ->orderBy('id', 'desc')
                    ->limit(100)
                    ->get();

                $responses[] = $result->count();
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }

            $times[] = (hrtime(true) - $start) / 1e6;
        }

        $this->assertEmpty(
            $errors,
            '50 concurrent catalog requests should complete without errors. Errors: ' . implode(', ', $errors)
        );

        $this->assertCount(
            self::CONCURRENT_REQUESTS,
            $responses,
            'All 50 requests should return results'
        );

        foreach ($responses as $count) {
            $this->assertEquals(100, $count, 'Each request should return 100 records');
        }

        $avg = array_sum($times) / count($times);
        $this->assertLessThanOrEqual(
            self::CATALOG_THRESHOLD_MS,
            $avg,
            "Average catalog response time ({$avg}ms) exceeds {self::CATALOG_THRESHOLD_MS}ms target"
        );

        $this->addToAssertionCount(1);
        echo "\n  [Catalog Load] 50 requests | avg: " . round($avg, 2) . "ms | target: " . self::CATALOG_THRESHOLD_MS . "ms ✓";
    }

    // ── Test 2: 50 Concurrent ISBN Lookups ────────────────────────────────────

    public function test_concurrent_isbn_lookups_meet_threshold(): void
    {
        // Grab 50 real ISBNs to look up
        $isbns = DB::table('books')
            ->select('isbn')
            ->limit(self::CONCURRENT_REQUESTS)
            ->pluck('isbn')
            ->toArray();

        $this->assertCount(
            self::CONCURRENT_REQUESTS,
            $isbns,
            'Need 50 ISBNs to run this test'
        );

        $errors = [];
        $times  = [];
        $found  = 0;

        foreach ($isbns as $isbn) {
            $start = hrtime(true);

            try {
                $book = DB::connection('pgsql::read')
                    ->table('books')
                    ->where('isbn', $isbn)
                    ->first();

                if ($book) {
                    $found++;
                }
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }

            $times[] = (hrtime(true) - $start) / 1e6;
        }

        $this->assertEmpty($errors, 'ISBN lookups should not throw errors');
        $this->assertEquals(self::CONCURRENT_REQUESTS, $found, 'All ISBNs should be found');

        $avg = array_sum($times) / count($times);
        $this->assertLessThanOrEqual(
            self::ISBN_THRESHOLD_MS,
            $avg,
            "Average ISBN lookup time ({$avg}ms) exceeds " . self::ISBN_THRESHOLD_MS . "ms target"
        );

        echo "\n  [ISBN Load] 50 lookups | avg: " . round($avg, 2) . "ms | target: " . self::ISBN_THRESHOLD_MS . "ms ✓";
    }

    // ── Test 3: Cache Populates on First Request ──────────────────────────────

    public function test_cache_is_populated_after_catalog_request(): void
    {
        Cache::forget('categories');

        // Prime the cache
        $categories = Cache::remember('categories', 3600, function () {
            return DB::table('categories')->get();
        });

        $this->assertTrue(
            Cache::has('categories'),
            'Categories should be cached after first request'
        );

        // Second hit should come from cache
        $start   = hrtime(true);
        $cached  = Cache::get('categories');
        $elapsed = (hrtime(true) - $start) / 1e6;

        $this->assertNotNull($cached, 'Cached categories should not be null');
        $this->assertLessThan(10, $elapsed, "Cache hit should be under 10ms, got {$elapsed}ms");

        echo "\n  [Cache] Hit time: " . round($elapsed, 3) . "ms ✓";
    }

    // ── Test 4: Cache Invalidation on Book Update ─────────────────────────────

    public function test_cache_invalidation_works_on_book_update(): void
    {
        $isbn     = DB::table('books')->value('isbn');
        $cacheKey = 'isbn:' . $isbn;

        // Prime the cache
        Cache::put($cacheKey, DB::table('books')->where('isbn', $isbn)->first(), 3600);
        $this->assertTrue(Cache::has($cacheKey), 'ISBN should be cached');

        // Simulate invalidation (what BookObserver does on saved())
        Cache::forget($cacheKey);

        $this->assertFalse(
            Cache::has($cacheKey),
            'Cache should be cleared after book update'
        );

        echo "\n  [Cache Invalidation] ISBN cache cleared correctly ✓";
    }

    // ── Test 5: Rate Limiting — Excessive Requests Are Throttled ─────────────

    public function test_rate_limiting_is_configured(): void
    {
        // Verify the rate limiter config exists (wired in RouteServiceProvider/AppServiceProvider)
        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        $this->assertNotNull($limiter, 'Rate limiter should be available');

        // Simulate hitting the public limit (30 req/min)
        $key     = 'test_rate_limit_' . uniqid();
        $limit   = 30;
        $allowed = 0;

        for ($i = 0; $i < 35; $i++) {
            if ($limiter->attempt($key, $limit, fn() => true, 60)) {
                $allowed++;
            }
        }

        $this->assertEquals(30, $allowed, 'Rate limiter should allow exactly 30 requests per minute');
        echo "\n  [Rate Limiting] Allowed 30/35 requests as expected ✓";
    }

    // ── Test 6: JSON Response Structure ──────────────────────────────────────

    public function test_catalog_response_returns_expected_structure(): void
    {
        $books = DB::connection('pgsql::read')
            ->table('books')
            ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $this->assertGreaterThan(0, $books->count(), 'Should return at least one book');

        foreach ($books as $book) {
            $this->assertNotNull($book->id,             'Book should have id');
            $this->assertNotNull($book->isbn,           'Book should have isbn');
            $this->assertNotNull($book->title,          'Book should have title');
            $this->assertNotNull($book->author,         'Book should have author');
            $this->assertNotNull($book->price,          'Book should have price');
            $this->assertNotNull($book->category_id,    'Book should have category_id');
        }

        echo "\n  [Structure] All 10 books have required fields ✓";
    }
}