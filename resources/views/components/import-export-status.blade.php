{{-- ── Import / Export Status Widget ──────────────────────────────────────── --}}
<div class="col-lg-8">
    <div class="card stretch stretch-full">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="feather feather-refresh-cw me-2 text-primary"></i>Import / Export Status
            </h5>
            <div class="hstack gap-2">
                <a href="{{ route('admin.import.logs') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="feather feather-upload" style="width:13px;height:13px;"></i> Import Logs
                </a>
                <a href="{{ route('admin.export.logs') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="feather feather-download" style="width:13px;height:13px;"></i> Export Logs
                </a>
            </div>
        </div>
        <div class="card-body">

            {{-- Summary stat pills --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fs-4 fw-bold text-primary">{{ $importStats['total'] }}</div>
                        <small class="text-muted">Total Imports</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fs-4 fw-bold text-success">{{ $importStats['completed'] }}</div>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fs-4 fw-bold text-danger">{{ $importStats['failed'] }}</div>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fs-4 fw-bold text-warning">{{ $importStats['processing'] }}</div>
                        <small class="text-muted">In Queue</small>
                    </div>
                </div>
            </div>

            {{-- Recent imports --}}
            <h6 class="fw-semibold mb-2" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color: var(--bs-secondary-color, #6c757d);">
                Recent Imports
            </h6>
            @if($recentImports->isEmpty())
                <p class="text-muted small mb-3">No imports yet.</p>
            @else
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <tbody>
                            @foreach($recentImports as $imp)
                                <tr>
                                    <td style="width:34px;">
                                        <div class="avatar-text avatar-sm"
                                            style="background:#3454d1;color:#fff;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                            <i class="feather feather-file-text" style="font-size:.75rem;"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold" style="font-size:.84rem;">{{ Str::limit($imp->file_name, 30) }}</span>
                                        <small class="text-muted d-block">{{ $imp->user->name ?? 'System' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $cls = match($imp->status) {
                                                'completed'  => 'bg-success',
                                                'failed'     => 'bg-danger',
                                                'processing' => 'bg-warning text-dark',
                                                default      => 'bg-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $cls }}">{{ ucfirst($imp->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-semibold small">{{ number_format($imp->successful_rows) }} ok</span>
                                        @if($imp->failed_rows > 0)
                                            <span class="text-danger small"> · {{ number_format($imp->failed_rows) }} failed</span>
                                        @endif
                                        <small class="text-muted d-block">{{ $imp->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Recent exports --}}
            <h6 class="fw-semibold mb-2" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color: var(--bs-secondary-color, #6c757d);">
                Recent Exports
            </h6>
            @if($recentExports->isEmpty())
                <p class="text-muted small">No exports yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <tbody>
                            @foreach($recentExports as $exp)
                                <tr>
                                    <td style="width:34px;">
                                        <div class="avatar-text avatar-sm"
                                            style="background:#27ae60;color:#fff;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                            <i class="feather feather-download" style="font-size:.75rem;"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold" style="font-size:.84rem;">{{ Str::limit($exp->file_name ?? 'Queued…', 30) }}</span>
                                        <small class="text-muted d-block">{{ $exp->user->name ?? 'System' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $cls = match($exp->status) {
                                                'completed'  => 'bg-success',
                                                'failed'     => 'bg-danger',
                                                'queued'     => 'bg-info',
                                                default      => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $cls }}">{{ ucfirst($exp->status) }}</span>
                                        <span class="badge bg-secondary ms-1">{{ strtoupper($exp->format ?? 'csv') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold small">{{ number_format($exp->rows_exported ?? 0) }} rows</span>
                                        <small class="text-muted d-block">{{ $exp->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Today's summary sidebar card --}}
<div class="col-lg-4">
    <div class="card stretch stretch-full">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="feather feather-calendar me-2 text-info"></i>Today's Activity
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                <div class="hstack gap-3">
                    <div class="avatar-text avatar-md" style="background:#e8f0fe;color:#3454d1;border-radius:8px;">
                        <i class="feather feather-upload"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Imports Today</div>
                        <small class="text-muted">Files processed</small>
                    </div>
                </div>
                <span class="fs-5 fw-bold text-primary">{{ $importStats['today'] }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                <div class="hstack gap-3">
                    <div class="avatar-text avatar-md" style="background:#e6f9f0;color:#27ae60;border-radius:8px;">
                        <i class="feather feather-download"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Exports Today</div>
                        <small class="text-muted">Files generated</small>
                    </div>
                </div>
                <span class="fs-5 fw-bold text-success">{{ $exportStats['today'] }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-3">
                <div class="hstack gap-3">
                    <div class="avatar-text avatar-md" style="background:#fef3e2;color:#e67e22;border-radius:8px;">
                        <i class="feather feather-clock"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">In Queue</div>
                        <small class="text-muted">Being processed</small>
                    </div>
                </div>
                <span class="fs-5 fw-bold text-warning">{{ $importStats['processing'] }}</span>
            </div>

            <div class="mt-3 pt-3 border-top">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.import.form') }}" class="btn btn-sm btn-success flex-fill">
                        <i class="feather feather-upload me-1" style="width:13px;height:13px;"></i> Import
                    </a>
                    <a href="{{ route('admin.export.form') }}" class="btn btn-sm btn-secondary flex-fill">
                        <i class="feather feather-download me-1" style="width:13px;height:13px;"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>