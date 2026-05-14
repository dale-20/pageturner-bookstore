<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Order;
use App\Models\Notification;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\AuditRetentionCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Database Backup at 2 AM daily
        $schedule->command('backup:run')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onSuccess(function() {
                Log::info('Database backup completed successfully');
                
                // Notify admins of successful backup
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'backup_success',
                        'title' => 'Database Backup Completed',
                        'message' => 'Daily database backup completed successfully at ' . now()->format('Y-m-d H:i:s'),
                        'data' => ['timestamp' => now()],
                    ]);
                }
            })
            ->onFailure(function() {
                Log::error('Database backup failed');
                
                // Notify admins of backup failure
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'backup_failure',
                        'title' => 'Database Backup Failed!',
                        'message' => 'CRITICAL: Daily database backup failed. Please check the system immediately.',
                        'data' => ['timestamp' => now()],
                    ]);
                }
            });

        // Cleanup old backups at 3 AM daily (Spatie's built-in command)
        $schedule->command('backup:clean')
            ->dailyAt('03:00')
            ->withoutOverlapping();

        // Monitor backup health hourly (custom command)
        $schedule->command('backup:monitor')
            ->hourly();

        // Cancel pending orders older than 24 hours
        $schedule->call(function () {
            $cancelledOrders = Order::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
            
            foreach ($cancelledOrders as $order) {
                $order->update(['status' => 'cancelled']);
                
                // Notify user
                Notification::create([
                    'user_id' => $order->user_id,
                    'type' => 'order_cancelled',
                    'title' => 'Order Cancelled',
                    'message' => "Your order #{$order->id} has been automatically cancelled as it was pending for more than 24 hours.",
                    'data' => ['order_id' => $order->id],
                ]);
            }
            
            $count = $cancelledOrders->count();
            Log::info("Cancelled {$count} expired pending orders");
        })->hourly()->name('orders:cleanup-pending');

        // Clean expired sessions
        $schedule->command('session:gc')
            ->daily()
            ->name('session:cleanup');

        // Archive old audit logs (older than 1 year)
        $schedule->call(function () {
            $archived = Audit::where('created_at', '<', now()->subYear())->delete();
            Log::info("Archived {$archived} old audit logs");
        })->monthly()->name('audit:archive');

        // Prune old notifications (older than 90 days)
        $schedule->call(function () {
            $pruned = Notification::where('created_at', '<', now()->subDays(90))->delete();
            Log::info("Pruned {$pruned} old notifications");
        })->weekly()->name('notification:prune');

        // Generate daily sales report at 6 AM
        $schedule->call(function () {
            $salesData = DB::table('orders')
                ->whereDate('created_at', now()->subDay())
                ->where('status', 'completed')
                ->selectRaw('SUM(total_amount) as total_sales, COUNT(*) as total_orders')
                ->first();
            
            Log::info('Daily Sales Report - Yesterday', (array)$salesData);
            
            // Notify admins with daily report
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'daily_report',
                    'title' => 'Daily Sales Report',
                    'message' => "Yesterday's sales: ₱" . number_format($salesData->total_sales ?? 0, 2) . 
                                " from " . ($salesData->total_orders ?? 0) . " orders",
                    'data' => (array)$salesData,
                ]);
            }
        })->dailyAt('06:00')->name('report:daily-sales');

        // Health check every 30 minutes
        $schedule->call(function () {
            $failedJobsCount = DB::table('failed_jobs')->count();
            $queueSize = DB::table('jobs')->count();
            $storageFree = disk_free_space(storage_path()) / 1024 / 1024 / 1024;
            
            $healthStatus = [
                'database' => true,
                'queue_size' => $queueSize,
                'failed_jobs' => $failedJobsCount,
                'storage_free_gb' => round($storageFree, 2),
                'timestamp' => now(),
            ];
            
            if ($failedJobsCount > 10) {
                Log::warning('High number of failed jobs detected', $healthStatus);
                
                // Notify admins
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'system_health',
                        'title' => 'System Health Alert',
                        'message' => "Warning: {$failedJobsCount} failed jobs detected in the queue.",
                        'data' => $healthStatus,
                    ]);
                }
            }
            
            if ($storageFree < 1) {
                Log::warning('Low storage space detected', $healthStatus);
                
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'system_health',
                        'title' => 'Low Storage Space',
                        'message' => "Warning: Only " . round($storageFree, 2) . " GB of storage remaining.",
                        'data' => $healthStatus,
                    ]);
                }
            }
        })->everyThirtyMinutes()->name('system:health-check');
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'admin' => \App\Http\Middleware\CheckAdmin::class,
            'redirect.role' => \App\Http\Middleware\RedirectBasedOnRole::class,
            'redirect.books.index' => \App\Http\Middleware\RedirectBookIndex::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'audit' => \App\Http\Middleware\AuditMiddleware::class,
        ]);

        $middleware->web([
            \App\Http\Middleware\EnsureTwoFactorVerified::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\AuditMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();