{{-- ── System Health Widget ─────────────────────────────────────────────────── --}}
<div class="col-lg-6">
    <div class="card stretch stretch-full">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="feather feather-activity me-2 text-success"></i>System Health
            </h5>
            @if(($systemHealth['failed_jobs'] ?? 0) > 0)
                <span class="badge bg-danger">
                    {{ $systemHealth['failed_jobs'] }} Failed Job{{ $systemHealth['failed_jobs'] !== 1 ? 's' : '' }}
                </span>
            @else
                <span class="badge bg-success">Healthy</span>
            @endif
        </div>
        <div class="card-body">

            {{-- Database & Storage --}}
            <h6 class="fw-semibold mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color: var(--bs-secondary-color, #6c757d);">
                Storage & Database
            </h6>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">
                        <i class="feather feather-database me-1 text-primary" style="font-size:.8rem;"></i>Database Size
                    </span>
                    <span class="small fw-bold">{{ number_format($systemHealth['db_size_mb'], 1) }} MB</span>
                </div>
                @php
                    $dbPct = min(100, ($systemHealth['db_size_mb'] / 500) * 100); // threshold: 500MB
                    $dbColor = $dbPct > 80 ? 'bg-danger' : ($dbPct > 50 ? 'bg-warning' : 'bg-success');
                @endphp
                <div class="progress" style="height:6px; border-radius:3px;">
                    <div class="progress-bar {{ $dbColor }}" style="width: {{ $dbPct }}%;" role="progressbar"></div>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">
                        <i class="feather feather-hard-drive me-1 text-info" style="font-size:.8rem;"></i>Storage Usage
                    </span>
                    <span class="small fw-bold">{{ number_format($systemHealth['storage_mb'], 1) }} MB</span>
                </div>
                @php
                    $stPct = min(100, ($systemHealth['storage_mb'] / 1024) * 100); // threshold: 1GB
                    $stColor = $stPct > 80 ? 'bg-danger' : ($stPct > 50 ? 'bg-warning' : 'bg-success');
                @endphp
                <div class="progress" style="height:6px; border-radius:3px;">
                    <div class="progress-bar {{ $stColor }}" style="width: {{ $stPct }}%;" role="progressbar"></div>
                </div>
            </div>

            {{-- Queue & Jobs --}}
            <h6 class="fw-semibold mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color: var(--bs-secondary-color, #6c757d);">
                Queue & Jobs
            </h6>

            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div class="hstack gap-2">
                    <i class="feather feather-loader text-info" style="font-size:.9rem;"></i>
                    <span class="small fw-semibold">Pending Jobs</span>
                </div>
                <span class="badge {{ $systemHealth['pending_jobs'] > 0 ? 'bg-info' : 'bg-success' }}">
                    {{ number_format($systemHealth['pending_jobs']) }}
                </span>
            </div>

            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div class="hstack gap-2">
                    <i class="feather feather-x-circle text-danger" style="font-size:.9rem;"></i>
                    <span class="small fw-semibold">Failed Jobs</span>
                </div>
                <span class="badge {{ $systemHealth['failed_jobs'] > 0 ? 'bg-danger' : 'bg-success' }}">
                    {{ number_format($systemHealth['failed_jobs']) }}
                </span>
            </div>

            {{-- Environment Info --}}
            <h6 class="fw-semibold mb-3 mt-4" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color: var(--bs-secondary-color, #6c757d);">
                Environment
            </h6>

            <div class="row g-2">
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fw-bold small">PHP {{ $systemHealth['php_version'] }}</div>
                        <small class="text-muted" style="font-size:.7rem;">Runtime</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fw-bold small">Laravel {{ $systemHealth['laravel_ver'] }}</div>
                        <small class="text-muted" style="font-size:.7rem;">Framework</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fw-bold small text-uppercase">{{ $systemHealth['cache_driver'] }}</div>
                        <small class="text-muted" style="font-size:.7rem;">Cache Driver</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                        <div class="fw-bold small text-uppercase">{{ $systemHealth['queue_driver'] }}</div>
                        <small class="text-muted" style="font-size:.7rem;">Queue Driver</small>
                    </div>
                </div>
            </div>

            {{-- Failed jobs alert --}}
            @if($systemHealth['failed_jobs'] > 0)
                <div class="alert alert-danger d-flex align-items-center gap-2 mt-3 py-2 mb-0" style="font-size:.84rem;">
                    <i class="feather feather-alert-circle flex-shrink-0"></i>
                    <div>
                        <strong>{{ $systemHealth['failed_jobs'] }} failed job{{ $systemHealth['failed_jobs'] !== 1 ? 's' : '' }}</strong>
                        require attention. Run <code>php artisan queue:retry all</code> or check Horizon.
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>