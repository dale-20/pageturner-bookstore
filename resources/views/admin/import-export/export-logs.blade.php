@extends('layouts.admin-layout')

@section('title', 'Export Logs - PageTurner')
@section('page-title', 'Export Logs')
@section('breadcrumb', 'Export Logs')

@section('add-features')
    <a href="{{ route('admin.export.form') }}" class="btn btn-sm btn-secondary">
        <i data-feather="download" class="me-1" style="width:14px; height:14px;"></i>
        <span>Export</span>
    </a>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="exportLogsList">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Exported By</th>
                                <th>Format</th>
                                <th>Status</th>
                                <th>Rows</th>
                                <th>Expires</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="single-item">
                                    <td>
                                        <div class="hstack gap-3">
                                            <div class="avatar-text avatar-md"
                                                style="background: #3454d1; color: #fff; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="feather feather-download" style="font-size: .9rem;"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-truncate-1-line" title="{{ $log->file_name ?? 'Queued' }}">
                                                    {{ $log->file_name ?? 'Queued…' }}
                                                </span>
                                                <small class="text-muted d-block">ID: {{ $log->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span>{{ $log->user->name ?? 'System' }}</span>
                                            <small class="text-muted d-block">{{ $log->user->email ?? '—' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary px-3 py-2 text-uppercase">
                                            {{ $log->format ?? 'csv' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($log->status) {
                                                'completed'  => 'bg-success',
                                                'processing' => 'bg-warning text-dark',
                                                'queued'     => 'bg-info',
                                                'failed'     => 'bg-danger',
                                                default      => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }} px-3 py-2">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($log->rows_exported ?? 0) }}</span>
                                        <small class="text-muted d-block">rows</small>
                                    </td>
                                    <td>
                                        @if($log->expires_at)
                                            @if($log->expires_at->isPast())
                                                <span class="text-danger small">Expired</span>
                                            @else
                                                <span class="small">{{ $log->expires_at->format('M d, Y') }}</span>
                                                <small class="text-muted d-block">{{ $log->expires_at->diffForHumans() }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $log->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            @if($log->status === 'completed' && $log->download_path && !($log->expires_at?->isPast()))
                                                <a href="{{ url('storage/' . $log->download_path) }}"
                                                    class="avatar-text avatar-md bg-success text-white"
                                                    data-bs-toggle="tooltip" title="Download File"
                                                    target="_blank">
                                                    <i class="feather feather-download"></i>
                                                </a>
                                            @else
                                                <span class="avatar-text avatar-md text-muted"
                                                    style="cursor: default; opacity: .4;"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $log->expires_at?->isPast() ? 'File expired' : 'Not available' }}">
                                                    <i class="feather feather-download"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($logs->hasPages())
                <div class="card-footer border-top py-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <small class="text-muted">
                            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                        </small>
                        <nav aria-label="Export logs pagination">
                            <ul class="pagination pagination-sm mb-0">
                                @if($logs->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="feather feather-chevron-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $logs->previousPageUrl() }}">
                                            <i class="feather feather-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach($logs->onEachSide(1)->links()->elements as $element)
                                    @if(is_string($element))
                                        <li class="page-item disabled">
                                            <span class="page-link">{{ $element }}</span>
                                        </li>
                                    @endif
                                    @if(is_array($element))
                                        @foreach($element as $page => $url)
                                            <li class="page-item {{ $page == $logs->currentPage() ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endforeach
                                    @endif
                                @endforeach

                                @if($logs->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $logs->nextPageUrl() }}">
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
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
@endsection