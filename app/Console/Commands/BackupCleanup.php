<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupCleanup extends Command
{
    protected $signature = 'backup:cleanup-old {--keep=7 : Number of backups to keep}';
    protected $description = 'Clean up old backup files';
    
    public function handle()
    {
        $keep = $this->option('keep');
        $backupDir = storage_path('app/private/Laravel');
        
        if (!file_exists($backupDir)) {
            $this->info('No backups directory found.');
            return Command::SUCCESS;
        }
        
        $files = glob($backupDir . '/*.zip');
        
        if (empty($files)) {
            $this->info('No backup files found.');
            return Command::SUCCESS;
        }
        
        // Sort by creation time (oldest first)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $toDelete = array_slice($files, 0, count($files) - $keep);
        $deleted = 0;
        
        foreach ($toDelete as $file) {
            if (unlink($file)) {
                $deleted++;
                $this->line("Deleted: " . basename($file));
            }
        }
        
        $this->info("Deleted {$deleted} old backup(s). Kept {$keep} most recent.");
        Log::info('Backup cleanup completed', ['deleted' => $deleted, 'kept' => $keep]);
        
        return Command::SUCCESS;
    }
}
