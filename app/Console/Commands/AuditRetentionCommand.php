<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditRetentionCommand extends Command
{
    protected $signature = 'audit:retain
                            {--dry-run : Show what would be archived/pruned without making changes}
                            {--archive-after=365 : Days before a record is moved to archive (default: 365)}
                            {--prune-after=1825 : Days before an archived record is deleted (default: 1825 = 5yrs)}';

    protected $description = 'Enforce audit log retention: archive records >1 year, prune archives >5 years.';

    public function handle(): int
    {
        $dryRun       = $this->option('dry-run');
        $archiveAfter = (int) $this->option('archive-after');
        $pruneAfter   = (int) $this->option('prune-after');

        $archiveCutoff = now()->subDays($archiveAfter);
        $pruneCutoff   = now()->subDays($pruneAfter);

        $this->info($dryRun ? '[DRY RUN] No changes will be made.' : 'Running audit retention...');
        $this->newLine();

        // ------------------------------------------------------------------
        // Step 1 — Ensure the audit_archives table exists
        // ------------------------------------------------------------------
        if (!Schema::hasTable('audit_archives')) {
            if ($dryRun) {
                $this->warn('audit_archives table does not exist — it will be created on first real run.');
            } else {
                $this->createArchiveTable();
                $this->info('Created audit_archives table.');
            }
        }

        // ------------------------------------------------------------------
        // Step 2 — Move records older than $archiveAfter days to archive
        // ------------------------------------------------------------------
        $toArchiveCount = DB::table('audits')
            ->where('created_at', '<', $archiveCutoff)
            ->count();

        $this->line("Records to archive (older than {$archiveAfter} days): <comment>{$toArchiveCount}</comment>");

        if (!$dryRun && $toArchiveCount > 0) {
            // Copy in chunks to avoid locking the table
            DB::table('audits')
                ->where('created_at', '<', $archiveCutoff)
                ->orderBy('id')
                ->chunkById(500, function ($rows) {
                    DB::table('audit_archives')->insert(
                        collect($rows)->map(fn($r) => (array) $r)->toArray()
                    );

                    $ids = collect($rows)->pluck('id')->toArray();
                    DB::table('audits')->whereIn('id', $ids)->delete();
                });

            $this->info("Archived {$toArchiveCount} records.");
        }

        // ------------------------------------------------------------------
        // Step 3 — Prune archive records older than $pruneAfter days
        // ------------------------------------------------------------------
        $toPruneCount = Schema::hasTable('audit_archives')
            ? DB::table('audit_archives')->where('created_at', '<', $pruneCutoff)->count()
            : 0;

        $this->line("Records to prune from archive (older than {$pruneAfter} days): <comment>{$toPruneCount}</comment>");

        if (!$dryRun && $toPruneCount > 0) {
            DB::table('audit_archives')
                ->where('created_at', '<', $pruneCutoff)
                ->orderBy('id')
                ->chunkById(500, function ($rows) {
                    $ids = collect($rows)->pluck('id')->toArray();
                    DB::table('audit_archives')->whereIn('id', $ids)->delete();
                });

            $this->info("Pruned {$toPruneCount} records from archive.");
        }

        $this->newLine();
        $this->info('Audit retention complete.');

        return self::SUCCESS;
    }

    protected function createArchiveTable(): void
    {
        // Mirror the audits table structure exactly so records copy cleanly
        DB::statement('CREATE TABLE audit_archives AS SELECT * FROM audits WHERE 1=0');

        // Add an archived_at timestamp
        Schema::table('audit_archives', function ($table) {
            $table->timestamp('archived_at')->nullable();
        });
    }
}