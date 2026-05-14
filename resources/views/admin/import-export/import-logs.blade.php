@extends('layouts.admin-layout')

@section('title', 'Import Logs - PageTurner')
@section('page-title', 'Import Logs')
@section('breadcrumb', 'Import Logs')

@section('add-features')
    <a href="{{ route('admin.import.form') }}" class="btn btn-sm btn-success">
        <i data-feather="upload" class="me-1" style="width:14px; height:14px;"></i>
        <span>Import</span>
    </a>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="importLogsList">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Imported By</th>
                                <th>Status</th>
                                <th>Rows</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="single-item">
                                    <td>
                                        <div class="hstack gap-3">
                                            <div class="avatar-text avatar-md"
                                                style="background: #3454d1; color: #fff; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="feather feather-file-text" style="font-size: .9rem;"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-truncate-1-line" title="{{ $log->file_name }}">
                                                    {{ $log->file_name }}
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
                                        <div class="d-flex flex-column">
                                            <span>
                                                <span class="text-success fw-semibold">{{ number_format($log->successful_rows) }}</span>
                                                <span class="text-muted"> ok</span>
                                                @if($log->failed_rows > 0)
                                                    &nbsp;·&nbsp;
                                                    <span class="text-danger fw-semibold">{{ number_format($log->failed_rows) }}</span>
                                                    <span class="text-muted"> failed</span>
                                                @endif
                                            </span>
                                            <small class="text-muted">of {{ number_format($log->total_rows) }} total</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $log->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
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
                        <nav aria-label="Import logs pagination">
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