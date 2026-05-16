<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshMaterializedViews extends Command
{
    protected $signature = 'app:refresh-materialized-views
                            {--concurrently : Refresh without locking reads (requires unique index)}';

    protected $description = 'Refresh all materialized views for reporting';

    public function handle(): int
    {
        $views = [
            'mv_bestseller_stats',
            'mv_inventory_summary',
        ];

        $concurrently = $this->option('concurrently') ? 'CONCURRENTLY' : '';

        foreach ($views as $view) {
            $this->info("Refreshing {$view}...");
            $start = microtime(true);

            try {
                DB::statement("REFRESH MATERIALIZED VIEW {$concurrently} {$view}");
                $elapsed = round((microtime(true) - $start) * 1000, 2);
                $this->info("  ✓ Done in {$elapsed}ms");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        $this->info('All materialized views refreshed.');
        return self::SUCCESS;
    }
}