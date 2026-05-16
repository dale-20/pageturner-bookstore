<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BenchmarkBookQueries extends Command
{
    protected $signature = 'benchmark:books
                            {--iterations=100 : Number of iterations per test}
                            {--warmup=5 : Warmup iterations before measuring}';

    protected $description = 'Benchmark critical book queries against performance targets';

    // ── Performance targets from Lab 7 Section 3.2.2 ─────────────────────────
    private array $targets = [
        'Catalog Listing'    => 100,
        'ISBN Lookup'        => 50,
        'Category Filter'    => 150,
        'Full-Text Search'   => 300,
        'Export 10K Chunk'   => 30000, // 30s in ms
    ];

    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $warmup     = (int) $this->option('warmup');

        $this->info("PageTurner Benchmark — {$iterations} iterations, {$warmup} warmup passes");
        $this->newLine();

        $results = [];
        $passed  = 0;
        $failed  = 0;

        // ── Test 1: Catalog Listing ───────────────────────────────────────────
        $results['Catalog Listing'] = $this->benchmark(
            'Catalog Listing (100 records, cursor paginate)',
            $iterations,
            $warmup,
            function () {
                DB::connection('pgsql::read')
                    ->table('books')
                    ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id'])
                    ->orderBy('id', 'desc')
                    ->limit(100)
                    ->get();
            }
        );

        // ── Test 2: ISBN Lookup ───────────────────────────────────────────────
        // Grab a real ISBN first
        $sampleIsbn = DB::table('books')->value('isbn');

        $results['ISBN Lookup'] = $this->benchmark(
            'ISBN Exact Match (unique index)',
            $iterations,
            $warmup,
            function () use ($sampleIsbn) {
                DB::connection('pgsql::read')
                    ->table('books')
                    ->where('isbn', $sampleIsbn)
                    ->first();
            }
        );

        // ── Test 3: Category Filter ───────────────────────────────────────────
        $sampleCategoryId = DB::table('books')->value('category_id');

        $results['Category Filter'] = $this->benchmark(
            'Category Filter (composite index)',
            $iterations,
            $warmup,
            function () use ($sampleCategoryId) {
                DB::connection('pgsql::read')
                    ->table('books')
                    ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id'])
                    ->where('category_id', $sampleCategoryId)
                    ->orderBy('id', 'desc')
                    ->limit(100)
                    ->get();
            }
        );

        // ── Test 4: Full-Text Search ──────────────────────────────────────────
        $iterations4 = min($iterations, 50); // Lab spec: 50 iterations for FTS

        $results['Full-Text Search'] = $this->benchmark(
            'Full-Text Search via GIN index (50 iterations)',
            $iterations4,
            $warmup,
            function () {
                DB::connection('pgsql::read')
                    ->table('books')
                    ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id'])
                    ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", ['science fiction'])
                    ->orderBy('id', 'desc')
                    ->limit(100)
                    ->get();
            }
        );

        // ── Test 5: Export 10K Chunk ──────────────────────────────────────────
        $results['Export 10K Chunk'] = $this->benchmark(
            'Export 10K record chunk (read replica)',
            5, // fewer iterations — this is a heavy query
            1,
            function () {
                DB::connection('pgsql::read')
                    ->table('books')
                    ->select(['isbn', 'title', 'author', 'price', 'stock_quantity'])
                    ->orderBy('id')
                    ->limit(10000)
                    ->get();
            }
        );

        // ── Results Table ─────────────────────────────────────────────────────
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info('  BENCHMARK RESULTS');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->newLine();

        $tableRows = [];
        foreach ($results as $name => $result) {
            $target     = $this->targets[$name];
            $pass       = $result['avg'] <= $target;
            $status     = $pass ? '✓ PASS' : '✗ FAIL';
            $pass ? $passed++ : $failed++;

            $tableRows[] = [
                $name,
                round($result['avg'], 2) . ' ms',
                round($result['min'], 2) . ' ms',
                round($result['max'], 2) . ' ms',
                $target . ' ms',
                $status,
            ];
        }

        $this->table(
            ['Test', 'Avg', 'Min', 'Max', 'Target', 'Status'],
            $tableRows
        );

        $this->newLine();
        $this->info("Results: {$passed} passed, {$failed} failed out of " . count($results) . ' tests.');

        if ($failed > 0) {
            $this->warn('Some targets were not met. Consider checking indexes and query plans.');
            $this->warn('Run: php artisan db:verify-splitting');
            return self::FAILURE;
        }

        $this->info('✓ All performance targets met!');
        return self::SUCCESS;
    }

    private function benchmark(string $label, int $iterations, int $warmup, callable $fn): array
    {
        $this->line("  Running: {$label}");

        // Warmup — not measured
        for ($i = 0; $i < $warmup; $i++) {
            $fn();
        }

        $times = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start   = hrtime(true);
            $fn();
            $times[] = (hrtime(true) - $start) / 1e6; // nanoseconds → ms
        }

        $avg   = array_sum($times) / count($times);
        $min   = min($times);
        $max   = max($times);
        $total = array_sum($times);

        $this->line("    avg: " . round($avg, 2) . "ms | min: " . round($min, 2) . "ms | max: " . round($max, 2) . "ms");

        return compact('avg', 'min', 'max', 'total', 'times');
    }
}