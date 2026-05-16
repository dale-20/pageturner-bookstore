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
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── Core stats ────────────────────────────────────────────────────────
        $stats = [
            'totalBooks' => Book::count(),
            'totalOrders' => Order::count(),
            'totalUsers' => User::count(),
            'totalCategories' => Category::count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        // Single query for all order status counts
        $orderStatusRaw = Order::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $orderStatusSummary = [
            'pending' => $orderStatusRaw['pending'] ?? 0,
            'processing' => $orderStatusRaw['processing'] ?? 0,
            'completed' => $orderStatusRaw['completed'] ?? 0,
            'cancelled' => $orderStatusRaw['cancelled'] ?? 0,
        ];

        $recentOrders = Order::with(['user', 'orderItems'])
            ->latest()
            ->take(10)
            ->get();

        $recentReviews = Review::with(['user', 'book'])
            ->latest()
            ->take(8)
            ->get();

        // ── Import / Export ───────────────────────────────────────────────────
        $recentImports = ImportLog::with('user')->latest()->take(5)->get();
        $recentExports = ExportLog::with('user')->latest()->take(5)->get();

        $importCountsRaw = ImportLog::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $importStats = [
            'total' => ImportLog::count(),
            'completed' => $importCountsRaw['completed'] ?? 0,
            'failed' => $importCountsRaw['failed'] ?? 0,
            'processing' => ($importCountsRaw['processing'] ?? 0) + ($importCountsRaw['queued'] ?? 0),
            'today' => ImportLog::whereDate('created_at', today())->count(),
        ];

        $exportCountsRaw = ExportLog::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $exportStats = [
            'total' => ExportLog::count(),
            'completed' => $exportCountsRaw['completed'] ?? 0,
            'failed' => $exportCountsRaw['failed'] ?? 0,
            'today' => ExportLog::whereDate('created_at', today())->count(),
        ];

        // ── Audit Log Summary ─────────────────────────────────────────────────
        // Kept in its own try/catch — audits table may not exist in all envs.
        $auditSummary = [];
        $recentAuditLogs = collect();
        $bestsellerStats = collect();
        $inventorySummary = collect();

        try {
            $auditCountsRaw = DB::table('audits')
                ->selectRaw("event, count(*) as total")
                ->groupBy('event')
                ->pluck('total', 'event');

            $auditSummary = [
                'total' => array_sum($auditCountsRaw->toArray()),
                'today' => DB::table('audits')->whereDate('created_at', today())->count(),
                'critical' => DB::table('audits')
                    ->whereIn('event', ['deleted', 'restored'])
                    ->whereDate('created_at', today())
                    ->count(),
                'updates' => DB::table('audits')
                    ->where('event', 'updated')
                    ->whereDate('created_at', today())
                    ->count(),
            ];

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
            $bestsellerStats = DB::connection('pgsql::read')
                ->table('mv_bestseller_stats')
                ->join('categories', 'categories.id', '=', 'mv_bestseller_stats.category_id')
                ->select(
                    'categories.name as category_name',
                    'mv_bestseller_stats.total_books',
                    'mv_bestseller_stats.avg_price',
                    'mv_bestseller_stats.total_inventory',
                    'mv_bestseller_stats.bestseller_count',
                    'mv_bestseller_stats.latest_addition'
                )
                ->orderByDesc('mv_bestseller_stats.bestseller_count')
                ->get();

            $inventorySummary = DB::connection('pgsql::read')
                ->table('mv_inventory_summary')
                ->orderByDesc('total_value')
                ->get();
        } catch (\Exception $e) {
            // audits table may not exist yet — fail silently
        }

        // ── Materialized Views ────────────────────────────────────────────────
        // Separate try/catch so audit errors never hide reporting data.
        $bestsellerStats = collect();
        $inventorySummary = collect();
        try {
            $bestsellerStats = DB::table('mv_bestseller_stats')
                ->join('categories', 'categories.id', '=', 'mv_bestseller_stats.category_id')
                ->select(
                    'categories.name as category_name',
                    'mv_bestseller_stats.total_books',
                    'mv_bestseller_stats.avg_price',
                    'mv_bestseller_stats.total_inventory',
                    'mv_bestseller_stats.bestseller_count',
                    'mv_bestseller_stats.latest_addition'
                )
                ->orderByDesc('mv_bestseller_stats.bestseller_count')
                ->get();

            $inventorySummary = DB::table('mv_inventory_summary')
                ->orderByDesc('total_value')
                ->get();
        } catch (\Exception $e) {
            // Views not yet created — show empty tables gracefully
        }

        // ── System Health ─────────────────────────────────────────────────────
        $systemHealth = [];
        try {
            $dbName = config('database.connections.' . config('database.default') . '.database');
            $dbSizeRaw = DB::select("SELECT pg_database_size(?) AS size_bytes", [$dbName]);
            $dbSizeMb = isset($dbSizeRaw[0]) ? round($dbSizeRaw[0]->size_bytes / 1024 / 1024, 2) : 0;

            $storagePath = storage_path('app/public');
            $storageBytes = 0;
            if (is_dir($storagePath)) {
                $output = shell_exec("du -sb " . escapeshellarg($storagePath) . " 2>/dev/null");
                if ($output) {
                    $storageBytes = (int) explode("\t", trim($output))[0];
                }
            }

            $failedJobs = DB::table('failed_jobs')->count();
            $pendingJobs = 0;
            try {
                $pendingJobs = DB::table('jobs')->count();
            } catch (\Exception $e) {
            }

            $systemHealth = [
                'db_size_mb' => $dbSizeMb,
                'storage_mb' => round($storageBytes / 1024 / 1024, 2),
                'failed_jobs' => $failedJobs,
                'pending_jobs' => $pendingJobs,
                'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
                'laravel_ver' => app()->version(),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
            ];
        } catch (\Exception $e) {
            $systemHealth = [
                'db_size_mb' => 0,
                'storage_mb' => 0,
                'failed_jobs' => 0,
                'pending_jobs' => 0,
                'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
                'laravel_ver' => app()->version(),
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
            'systemHealth',
            'bestsellerStats',
            'inventorySummary'
        ));
    }

    public function books(Request $request)
    {
        $searchTerm = trim($request->get('search', ''));
        $categoryId = $request->get('category');
        $isbn = trim($request->get('isbn', ''));

        // ── ISBN exact lookup — hits idx_books_isbn_lookup directly ──────────
        if (!empty($isbn)) {
            $books = Book::with('category')->where('isbn', $isbn)->cursorPaginate(50);
            $categories = Category::all();
            return view('admin.books', compact('books', 'categories'));
        }

        // ── Meilisearch Scout search ──────────────────────────────────────────
        if (!empty($searchTerm)) {
            $scoutOptions = [];
            if (!empty($categoryId)) {
                $scoutOptions['filter'] = "category_id = {$categoryId}";
            }

            $books = Book::search($searchTerm, function ($meilisearch, string $query, array $options) use ($scoutOptions) {
                if (!empty($scoutOptions['filter'])) {
                    $options['filter'] = $scoutOptions['filter'];
                }
                return $meilisearch->search($query, $options);
            })
                ->query(fn($q) => $q->with('category:id,name'))
                ->paginate(50);

            $categories = Category::all();
            return view('admin.books', compact('books', 'categories'));
        }

        // ── Normal listing — GIN scope for short strings, index for category ─
        $query = Book::with('category');

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // cursorPaginate avoids OFFSET slowdown on deep pages of 1M rows
        $books = Book::on('pgsql::read')
            ->with('category')
            ->orderBy('id', 'desc')
            ->cursorPaginate(50);
        $categories = Category::all();

        return view('admin.books', compact('books', 'categories'));
    }

    public function bookShow(Book $book)
    {
        $book->load(['category', 'reviews.user']);

        $total_sold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.book_id', $book->id)
            ->where('orders.status', 'completed')
            ->sum('order_items.quantity');

        $revenue = $book->price * $total_sold;

        return view('admin.bookShow', compact('book', 'total_sold', 'revenue'));
    }

    public function orders(string $status)
    {
        $orders = Order::with(['user', 'orderItems.book'])
            ->where('status', $status)
            ->latest()
            ->cursorPaginate(50);

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
            return redirect()->route('admin.orders', 'pending')
                ->with('error', 'Order not found.');
        }

        return view('admin.orderShow', compact('order'));
    }

    public function users(string $role)
    {
        $users = User::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->where('role', $role)
            ->cursorPaginate(50);

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
            'name' => 'required|string|max:255',
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