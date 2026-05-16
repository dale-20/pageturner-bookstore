{{-- ── Audit Log Summary Widget ─────────────────────────────────────────────── --}}
<div class="col-lg-6">
    <div class="card stretch stretch-full">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="feather feather-shield me-2 text-warning"></i>Audit Log Summary
            </h5>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-secondary">
                View All
            </a>
        </div>
        <div class="card-body">

            @if(empty($auditSummary))
                <div class="text-center py-4 text-muted">
                    <i class="feather feather-alert-circle" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 small">Audit log data unavailable.</p>
                </div>
            @else

                {{-- Summary counts --}}
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                            <div class="fs-4 fw-bold">{{ number_format($auditSummary['today']) }}</div>
                            <small class="text-muted">Events Today</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                            <div class="fs-4 fw-bold text-danger">{{ number_format($auditSummary['critical']) }}</div>
                            <small class="text-muted">Critical Today</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                            <div class="fs-4 fw-bold text-info">{{ number_format($auditSummary['updates']) }}</div>
                            <small class="text-muted">Updates Today</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded text-center" style="background: var(--bs-light, #f8f9fa);">
                            <div class="fs-4 fw-bold text-secondary">{{ number_format($auditSummary['total']) }}</div>
                            <small class="text-muted">Total Logs</small>
                        </div>
                    </div>
                </div>

                {{-- Critical events --}}
                <h6 class="fw-semibold mb-2" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color: var(--bs-secondary-color, #6c757d);">
                    Recent Critical Events
                </h6>

                @if($recentAuditLogs->isEmpty())
                    <div class="text-center py-3">
                        <i class="feather feather-check-circle text-success" style="font-size:1.5rem;"></i>
                        <p class="text-muted small mt-1 mb-0">No critical events today. All clear.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <tbody>
                                @foreach($recentAuditLogs as $log)
                                    <tr>
                                        <td style="width:34px;">
                                            @php
                                                $evtColor = match($log->event) {
                                                    'deleted'   => '#c0392b',
                                                    'restored'  => '#27ae60',
                                                    default     => '#7f8c8d',
                                                };
                                            @endphp
                                            <div class="avatar-text avatar-sm"
                                                style="background:{{ $evtColor }}20;color:{{ $evtColor }};display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                                <i class="feather feather-alert-triangle" style="font-size:.75rem;"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold" style="font-size:.84rem;">{{ $log->user_name ?? 'System' }}</span>
                                            <small class="text-muted d-block">{{ $log->user_email ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge"
                                                style="background:{{ $evtColor }}20;color:{{ $evtColor }};font-weight:700;">
                                                {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            @endif

        </div>
    </div>
</div>