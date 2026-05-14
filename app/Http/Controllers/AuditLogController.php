<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    // -------------------------------------------------------------------------
    // Index — filterable, searchable list
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $query = Audit::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('model_type')) {
            $query->where('auditable_type', 'like', '%' . $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%");
            });
        }

        $auditLogs = $query->paginate(50)->withQueryString();

        $users      = User::orderBy('name')->get();
        $events     = Audit::distinct()->pluck('event');
        $modelTypes = Audit::distinct()
            ->pluck('auditable_type')
            ->map(fn($t) => class_basename($t))
            ->unique()
            ->values();

        $stats = [
            'total_logs'      => Audit::count(),
            'today_logs'      => Audit::whereDate('created_at', today())->count(),
            'unique_users'    => Audit::distinct('user_id')->count('user_id'),
            'critical_events' => Audit::whereIn('event', [
                'deleted', 'role_assigned', 'role_revoked',
                'permission_granted', 'permission_revoked',
            ])->whereDate('created_at', today())->count(),
            'by_event'        => Audit::select('event', DB::raw('count(*) as total'))
                ->groupBy('event')
                ->get(),
        ];

        return view('admin.audit.index', compact('auditLogs', 'users', 'events', 'modelTypes', 'stats'));
    }

    // -------------------------------------------------------------------------
    // Show — single record detail with diff and checksum status
    // -------------------------------------------------------------------------

    public function show(int $id)
    {
        $auditLog = Audit::with('user')->findOrFail($id);

        $isVerified = $auditLog->verifyChecksum();

        // Already cast to arrays by $casts — no manual decode needed
        $oldValues = $auditLog->old_values;
        $newValues = $auditLog->new_values;

        // Build a unified diff: all keys from both old and new
        $diffKeys = array_unique(array_merge(
            array_keys($oldValues ?? []),
            array_keys($newValues ?? []),
        ));

        $diff = [];
        foreach ($diffKeys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;
            $diff[$key] = [
                'old'     => $old,
                'new'     => $new,
                'changed' => $old !== $new,
            ];
        }

        return view('admin.audit.show', compact('auditLog', 'isVerified', 'oldValues', 'newValues', 'diff'));
    }

    // -------------------------------------------------------------------------
    // Export — chunked CSV (Postgres-safe, memory-safe)
    // -------------------------------------------------------------------------

    public function export(Request $request)
    {
        $fileName = 'audit_logs_' . date('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/exports/' . $fileName);

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');
        fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        fputcsv($file, [
            'ID', 'User', 'Event', 'Model Type', 'Model ID',
            'Old Values', 'New Values', 'IP Address', 'URL',
            'Method', 'User Agent', 'Created At', 'Checksum Verified',
        ]);

        $query = Audit::with('user');

        if ($request->filled('user_id'))  $query->where('user_id', $request->user_id);
        if ($request->filled('event'))    $query->where('event', $request->event);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);

        $query->orderBy('id')->chunk(500, function ($logs) use ($file) {
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->email ?? 'System',
                    $log->event,
                    class_basename($log->auditable_type),
                    $log->auditable_id,
                    json_encode($log->old_values),
                    json_encode($log->new_values),
                    $log->ip_address,
                    $log->url,
                    $log->method,
                    $log->user_agent,
                    $log->created_at,
                    $log->verifyChecksum() ? 'Yes' : 'NO!',
                ]);
            }
        });

        fclose($file);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    // -------------------------------------------------------------------------
    // Verify integrity — chunked, memory-safe
    // -------------------------------------------------------------------------

    public function verifyIntegrity()
    {
        $tampered = [];
        $total    = 0;

        Audit::orderBy('id')->chunk(500, function ($logs) use (&$tampered, &$total) {
            foreach ($logs as $log) {
                $total++;
                if ($log->checksum && !$log->verifyChecksum()) {
                    $tampered[] = [
                        'id'         => $log->id,
                        'created_at' => $log->created_at,
                        'event'      => $log->event,
                        'model'      => class_basename($log->auditable_type),
                        'model_id'   => $log->auditable_id,
                    ];
                }
            }
        });

        return response()->json([
            'total'              => $total,
            'tampered'           => $tampered,
            'integrity_verified' => count($tampered) === 0,
        ]);
    }

    // -------------------------------------------------------------------------
    // Stats — Postgres-safe (no MySQL-only DATE()/HOUR() functions)
    // -------------------------------------------------------------------------

    public function stats()
    {
        $driver = DB::getDriverName();

        // Date truncation — Postgres vs MySQL vs SQLite
        $dateExpr = match ($driver) {
            'pgsql'  => "DATE_TRUNC('day', created_at)::date",
            'sqlite' => "strftime('%Y-%m-%d', created_at)",
            default  => 'DATE(created_at)',
        };

        // Hour extraction
        $hourExpr = match ($driver) {
            'pgsql'  => 'EXTRACT(HOUR FROM created_at)',
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            default  => 'HOUR(created_at)',
        };

        $stats = [
            'by_event' => Audit::select('event', DB::raw('count(*) as total'))
                ->groupBy('event')
                ->get(),

            'by_day' => Audit::select(
                DB::raw("{$dateExpr} as date"),
                DB::raw('count(*) as total')
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw($dateExpr))
                ->orderBy('date', 'desc')
                ->get(),

            'by_hour' => Audit::select(
                DB::raw("{$hourExpr} as hour"),
                DB::raw('count(*) as total')
            )
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy(DB::raw($hourExpr))
                ->orderBy('hour', 'asc')
                ->get(),
        ];

        return response()->json($stats);
    }
}
