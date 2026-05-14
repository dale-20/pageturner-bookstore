@extends('layouts.admin-layout')

@section('title', 'Audit Logs')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ number_format($stats['total_logs']) }}</div>
                    <div class="text-muted">Total Logs</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ number_format($stats['today_logs']) }}</div>
                    <div class="text-muted">Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ number_format($stats['unique_users']) }}</div>
                    <div class="text-muted">Users</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ number_format($stats['critical_events']) }}</div>
                    <div class="text-muted">Critical Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Event</label>
                    <select name="event" class="form-select">
                        <option value="">All</option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Model</label>
                    <select name="model_type" class="form-select">
                        <option value="">All</option>
                        @foreach ($modelTypes as $modelType)
                            <option value="{{ $modelType }}" @selected(request('model_type') === $modelType)>{{ $modelType }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-light">Reset</a>
                    <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-outline-secondary ms-auto">Export CSV</a>
                    <a href="{{ route('admin.audit.verify') }}" class="btn btn-outline-secondary">Verify Integrity</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Model</th>
                        <th>IP</th>
                        <th>Method</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user?->email ?? 'System' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $log->event }}</span></td>
                            <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->method }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.audit.show', $log) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($auditLogs->hasPages())
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
                    <small class="text-muted">
                        Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} of {{ $auditLogs->total() }} logs
                    </small>
                    <nav aria-label="Audit log pagination">
                        <ul class="pagination pagination-sm mb-0">
                            @if($auditLogs->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="feather feather-chevron-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $auditLogs->previousPageUrl() }}">
                                        <i class="feather feather-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach($auditLogs->onEachSide(1)->links()->elements as $element)
                                @if(is_string($element))
                                    <li class="page-item disabled">
                                        <span class="page-link">{{ $element }}</span>
                                    </li>
                                @endif
                                @if(is_array($element))
                                    @foreach($element as $page => $url)
                                        <li class="page-item {{ $page == $auditLogs->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endforeach
                                @endif
                            @endforeach

                            @if($auditLogs->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $auditLogs->nextPageUrl() }}">
                                        <i class="feather feather-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="feather feather-chevron-right"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection