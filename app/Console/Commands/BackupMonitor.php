<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupMonitor extends Command
{
    protected $signature = 'backup:monitor';
    protected $description = 'Monitor backup health and status';
    
    public function handle()
    {
        // The actual backup location from your system
        $backupDir = storage_path('app/private/Laravel');
        
        $this->info("Looking for backups in: {$backupDir}");
        
        $foundBackups = [];
        
        // Check the actual backup directory
        if (file_exists($backupDir)) {
            $files = glob($backupDir . '/*.zip');
            foreach ($files as $file) {
                $foundBackups[] = $file;
            }
        }
        
        // Also check pageturner-backup directory
        $ptBackupDir = storage_path('app/pageturner-backup');
        if (file_exists($ptBackupDir)) {
            $files = glob($ptBackupDir . '/*.zip');
            foreach ($files as $file) {
                $foundBackups[] = $file;
            }
        }
        
        if (empty($foundBackups)) {
            $this->warn('==========================================');
            $this->warn('⚠️  NO BACKUP FILES FOUND');
            $this->warn('==========================================');
            $this->info('');
            $this->info('Create a backup manually:');
            $this->info('  php artisan backup:run');
            $this->info('');
            return Command::FAILURE;
        }
        
        // Find the latest backup
        $latestFile = null;
        $latestTime = 0;
        
        foreach ($foundBackups as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latestTime) {
                $latestTime = $mtime;
                $latestFile = $file;
            }
        }
        
        $lastBackupTime = $latestTime;
        $hoursSinceBackup = round((time() - $lastBackupTime) / 3600, 1);
        $backupSize = round(filesize($latestFile) / 1024 / 1024, 2);
        $backupName = basename($latestFile);
        
        // Check disk space
        $freeSpace = disk_free_space(storage_path()) / 1024 / 1024 / 1024;
        
        // Determine health
        $isHealthy = true;
        $issues = [];
        
        if ($hoursSinceBackup > 48) {
            $isHealthy = false;
            $issues[] = "Last backup was {$hoursSinceBackup} hours ago (over 48 hours)";
        }
        
        if ($freeSpace < 1) {
            $isHealthy = false;
            $issues[] = "Low storage space: " . round($freeSpace, 2) . " GB remaining";
        }
        
        // Display report
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    BACKUP MONITOR REPORT                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info("  📍 Location:     storage/app/private/Laravel/");
        $this->info("  📄 File:         {$backupName}");
        $this->info("  📅 Last backup:  " . date('Y-m-d H:i:s', $lastBackupTime));
        $this->info("  ⏰ Hours ago:    {$hoursSinceBackup} hours");
        $this->info("  💾 Backup size:  {$backupSize} MB");
        $this->info("  💿 Free space:   " . round($freeSpace, 2) . " GB");
        $this->info("  📦 Total backups:" . count($foundBackups));
        $this->info('');
        
        if ($isHealthy && !empty($foundBackups)) {
            $this->info('  ✅ STATUS: HEALTHY - Backup system is working');
            $this->info('');
            Log::info('Backup health check: HEALTHY', [
                'last_backup' => date('Y-m-d H:i:s', $lastBackupTime),
                'backup_size_mb' => $backupSize,
                'total_backups' => count($foundBackups),
                'free_space_gb' => round($freeSpace, 2),
            ]);
        } else {
            $this->warn('  ⚠️  STATUS: UNHEALTHY - Issues detected');
            foreach ($issues as $issue) {
                $this->warn("  ⚠️  - {$issue}");
            }
            $this->info('');
            Log::warning('Backup health check: UNHEALTHY', ['issues' => $issues]);
        }
        
        return $isHealthy ? Command::SUCCESS : Command::FAILURE;
    }
}