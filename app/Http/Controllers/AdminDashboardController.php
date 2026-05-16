<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\ImportLog;
use App\Models\ExportLog;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalBooks'      => Book::count(),
            'totalOrders'     => Order::count(),
            'totalUsers'      => User::count(),
            'totalCategories' => Category::count(),
            'revenue'         => Order::where('status', 'completed')->sum('total_amount'),
        ];

        // Order status summary
        $orderStatusSummary = [
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed'  => Order::where('status', 'completed')->count(),
            'cancelled'  => Order::where('status', 'cancelled')->count(),
        ];

        $recentOrders = Order::with(['user', 'orderItems'])
            ->latest()
            ->take(10)
            ->get();

        $recentReviews = Review::with(['user', 'book'])
            ->latest()
            ->take(8)
            ->get();

        // ── Import / Export Status ────────────────────────────────────────────
        $recentImports = ImportLog::with('user')->latest()->take(5)->get();
        $recentExports = ExportLog::with('user')->latest()->take(5)->get();

        $importStats = [
            'total'      => ImportLog::count(),
            'completed'  => ImportLog::where('status', 'completed')->count(),
            'failed'     => ImportLog::where('status', 'failed')->count(),
            'processing' => ImportLog::where('status', 'processing')->orWhere('status', 'queued')->count(),
            'today'      => ImportLog::whereDate('created_at', today())->count(),
        ];
        $exportStats = [
            'total'      => ExportLog::count(),
            'completed'  => ExportLog::where('status', 'completed')->count(),
            'failed'     => ExportLog::where('status', 'failed')->count(),
            'today'      => ExportLog::whereDate('created_at', today())->count(),
        ];

        // ── Audit Log Summary ─────────────────────────────────────────────────
        // Uses the audit_logs table directly to avoid hard model dependency
        $auditSummary = [];
        $recentAuditLogs = collect();
        try {
            // owen-it/laravel-auditing stores records in the `audits` table.
            $auditSummary = [
                'total'    => DB::table('audits')->count(),
                'today'    => DB::table('audits')->whereDate('created_at', today())->count(),
                'critical' => DB::table('audits')
                    ->whereIn('event', ['deleted', 'restored'])
                    ->whereDate('created_at', today())
                    ->count(),
                'updates'  => DB::table('audits')
                    ->where('event', 'updated')
                    ->whereDate('created_at', today())
                    ->count(),
            ];
            // Left join so audits by deleted/null users still appear
            $recentAuditLogs = DB::table('audits')
                ->leftJoin('users', function ($join) {
                    $join->on('audits.user_id', '=', 'users.id')
                         ->where('audits.user_type', '=', \App\Models\User::class);
                })
                ->select('audits.*', 'users.name as user_name', 'users.email as user_email')
                ->whereIn('audits.event', ['deleted', 'restored'])
                ->latest('audits.created_at')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            // audits table may not exist yet
        }

        // ── System Health ─────────────────────────────────────────────────────
        $systemHealth = [];
        try {
            // Database size — PostgreSQL
            $dbName    = config('database.connections.' . config('database.default') . '.database');
            $dbSizeRaw = DB::select("SELECT pg_database_size(?) AS size_bytes", [$dbName]);
            $dbSizeMb  = isset($dbSizeRaw[0]) ? round($dbSizeRaw[0]->size_bytes / 1024 / 1024, 2) : 0;

            // Storage usage — storage/app/public only
            $storagePath  = storage_path('app/public');
            $storageBytes = 0;
            if (is_dir($storagePath)) {
                $output = shell_exec("du -sb " . escapeshellarg($storagePath) . " 2>/dev/null");
                if ($output) {
                    $storageBytes = (int) explode("\t", trim($output))[0];
                }
            }

            // Failed jobs
            $failedJobs  = DB::table('failed_jobs')->count();

            // Pending queue jobs (database driver)
            $pendingJobs = 0;
            try {
                $pendingJobs = DB::table('jobs')->count();
            } catch (\Exception $e) {}

            $systemHealth = [
                'db_size_mb'   => $dbSizeMb,
                'storage_mb'   => round($storageBytes / 1024 / 1024, 2),
                'failed_jobs'  => $failedJobs,
                'pending_jobs' => $pendingJobs,
                'php_version'  => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
                'laravel_ver'  => app()->version(),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
            ];
        } catch (\Exception $e) {
            $systemHealth = [
                'db_size_mb'   => 0,
                'storage_mb'   => 0,
                'failed_jobs'  => 0,
                'pending_jobs' => 0,
                'php_version'  => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
                'laravel_ver'  => app()->version(),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
            ];
        }

        return view('admin.index', compact(
            'stats',
            'orderStatusSummary',
            'recentOrders',
            'recentReviews',
            'recentImports',
            'recentExports',
            'importStats',
            'exportStats',
            'auditSummary',
            'recentAuditLogs',
            'systemHealth'
        ));
    }

    public function books(Request $request)
    {
        $books = Book::all()->sortByDesc('created_at');
        return view('admin.books', compact('books'));
    }

    public function bookShow(Book $book)
    {
        $book->load(['category', 'reviews.user']);
        $total_sold = Order::where('status', 'completed')
            ->whereHas('orderItems', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })->count();
        $revenue = $book->price * $total_sold;
        return view('admin.bookShow', compact('book', 'total_sold', 'revenue'));
    }

    public function orders(string $status)
    {
        $orders = Order::where('status', $status)->get();
        return view('admin.orders', compact('orders'));
    }

    public function orderStatus(Request $request, Order $order)
    {
        $validated = $request->validate(['status' => 'required']);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        $order->load('user');
        $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus));

        return redirect()->route('admin.orderShow', $order->id)
            ->with('success', 'Order successfully updated.');
    }

    public function orderShow(int $id)
    {
        $order = Order::with(['user', 'orderItems.book', 'orderItems.book.category'])->find($id);

        if (!$order) {
            return redirect()->route('admin.orders', 'pending')->with('error', 'Order not found.');
        }

        return view('admin.orderShow', compact('order'));
    }

    public function users(string $role)
    {
        $users = User::where('role', $role)->get();
        return view('admin.users', compact('users'));
    }

    public function userShow(User $user)
    {
        $user->loadCount('orders')->loadSum('orders', 'total_amount');
        return view('admin.userShow', compact('user'));
    }

    public function userEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function userUpdate(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function userDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }
}