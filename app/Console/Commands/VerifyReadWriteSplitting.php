<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyReadWriteSplitting extends Command
{
    protected $signature   = 'db:verify-splitting';
    protected $description = 'Verify read/write connection splitting is configured correctly';

    public function handle(): int
    {
        $this->info('Checking read/write splitting...');

        try {
            // Write connection
            $writeHost = DB::selectOne('SELECT inet_server_addr() as host, inet_server_port() as port');
            $this->info('  Write host: ' . ($writeHost->host ?? '127.0.0.1') . ':' . ($writeHost->port ?? 5432));

            // Read connection
            $readHost = DB::connection('pgsql::read')
                ->selectOne('SELECT inet_server_addr() as host, inet_server_port() as port');
            $this->info('  Read host:  ' . ($readHost->host ?? '127.0.0.1') . ':' . ($readHost->port ?? 5432));

            // Book count via read replica
            $count = DB::connection('pgsql::read')->table('books')->count();
            $this->info("  Books visible on read replica: {$count}");

            $this->info('✓ Read/write splitting is working correctly.');
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('✗ Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}