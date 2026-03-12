{{-- resources/views/components/order-status-summary.blade.php --}}

<div class="col-lg-12">
    <div class="card stretch stretch-full">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Order Status Summary</h5>
            <a href="{{ route('admin.orders', 'pending') }}" class="btn btn-sm btn-light-brand">
                Manage Orders <i class="feather-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body">

            {{-- Total Revenue --}}
            <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-4"
                 style="background: rgba(34,197,94,0.06); border: 1.5px solid rgba(34,197,94,0.2);">
                <div class="avatar-text avatar-lg" style="background: rgba(34,197,94,0.15); flex-shrink: 0;">
                    <i class="feather-dollar-sign" style="color: #22c55e;"></i>
                </div>
                <div>
                    <div class="fs-11 fw-semibold text-muted text-uppercase mb-1">Total Revenue (Completed Orders)</div>
                    <div class="fs-3 fw-bold" style="color: #22c55e;">&#8369;{{ number_format($stats['revenue'], 2) }}</div>
                </div>
            </div>

            <div class="row g-3">

                {{-- Pending --}}
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.orders', 'pending') }}" class="text-decoration-none">
                        <div class="p-3 rounded-3 text-center h-100"
                             style="background: rgba(255, 193, 7, 0.08); border: 1.5px solid rgba(255, 193, 7, 0.25); transition: all 0.2s;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(255,193,7,0.2)'"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div class="avatar-text avatar-lg mx-auto mb-3" style="background: rgba(255,193,7,0.15);">
                                <i class="feather-clock" style="color: #f59e0b;"></i>
                            </div>
                            <div class="fs-3 fw-bold" style="color: #f59e0b;">
                                {{ $orderStatusSummary['pending'] }}
                            </div>
                            <div class="fs-13 fw-semibold text-muted mt-1">Pending</div>
                        </div>
                    </a>
                </div>

                {{-- Processing --}}
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.orders', 'processing') }}" class="text-decoration-none">
                        <div class="p-3 rounded-3 text-center h-100"
                             style="background: rgba(59, 130, 246, 0.08); border: 1.5px solid rgba(59, 130, 246, 0.25); transition: all 0.2s;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(59,130,246,0.2)'"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div class="avatar-text avatar-lg mx-auto mb-3" style="background: rgba(59,130,246,0.15);">
                                <i class="feather-refresh-cw" style="color: #3b82f6;"></i>
                            </div>
                            <div class="fs-3 fw-bold" style="color: #3b82f6;">
                                {{ $orderStatusSummary['processing'] }}
                            </div>
                            <div class="fs-13 fw-semibold text-muted mt-1">Processing</div>
                        </div>
                    </a>
                </div>

                {{-- Completed --}}
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.orders', 'completed') }}" class="text-decoration-none">
                        <div class="p-3 rounded-3 text-center h-100"
                             style="background: rgba(34, 197, 94, 0.08); border: 1.5px solid rgba(34, 197, 94, 0.25); transition: all 0.2s;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(34,197,94,0.2)'"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div class="avatar-text avatar-lg mx-auto mb-3" style="background: rgba(34,197,94,0.15);">
                                <i class="feather-check-circle" style="color: #22c55e;"></i>
                            </div>
                            <div class="fs-3 fw-bold" style="color: #22c55e;">
                                {{ $orderStatusSummary['completed'] }}
                            </div>
                            <div class="fs-13 fw-semibold text-muted mt-1">Completed</div>
                        </div>
                    </a>
                </div>

                {{-- Cancelled --}}
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.orders', 'cancelled') }}" class="text-decoration-none">
                        <div class="p-3 rounded-3 text-center h-100"
                             style="background: rgba(239, 68, 68, 0.08); border: 1.5px solid rgba(239, 68, 68, 0.25); transition: all 0.2s;"
                             onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(239,68,68,0.2)'"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div class="avatar-text avatar-lg mx-auto mb-3" style="background: rgba(239,68,68,0.15);">
                                <i class="feather-x-circle" style="color: #ef4444;"></i>
                            </div>
                            <div class="fs-3 fw-bold" style="color: #ef4444;">
                                {{ $orderStatusSummary['cancelled'] }}
                            </div>
                            <div class="fs-13 fw-semibold text-muted mt-1">Cancelled</div>
                        </div>
                    </a>
                </div>

            </div>

            {{-- Progress bar breakdown --}}
            @php
                $total = array_sum($orderStatusSummary);
                $pct = fn($n) => $total > 0 ? round(($n / $total) * 100) : 0;
            @endphp
            @if($total > 0)
            <div class="mt-4">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted fw-semibold">Order Distribution</small>
                    <small class="text-muted">{{ $total }} total</small>
                </div>
                <div class="progress" style="height: 10px; border-radius: 10px; overflow: hidden;">
                    @if($orderStatusSummary['pending'] > 0)
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $pct($orderStatusSummary['pending']) }}%; background: #f59e0b;"
                         title="Pending: {{ $orderStatusSummary['pending'] }}"></div>
                    @endif
                    @if($orderStatusSummary['processing'] > 0)
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $pct($orderStatusSummary['processing']) }}%; background: #3b82f6;"
                         title="Processing: {{ $orderStatusSummary['processing'] }}"></div>
                    @endif
                    @if($orderStatusSummary['completed'] > 0)
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $pct($orderStatusSummary['completed']) }}%; background: #22c55e;"
                         title="Completed: {{ $orderStatusSummary['completed'] }}"></div>
                    @endif
                    @if($orderStatusSummary['cancelled'] > 0)
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $pct($orderStatusSummary['cancelled']) }}%; background: #ef4444;"
                         title="Cancelled: {{ $orderStatusSummary['cancelled'] }}"></div>
                    @endif
                </div>
                <div class="d-flex gap-3 mt-2 flex-wrap">
                    <small><span style="color:#f59e0b;">●</span> Pending {{ $pct($orderStatusSummary['pending']) }}%</small>
                    <small><span style="color:#3b82f6;">●</span> Processing {{ $pct($orderStatusSummary['processing']) }}%</small>
                    <small><span style="color:#22c55e;">●</span> Completed {{ $pct($orderStatusSummary['completed']) }}%</small>
                    <small><span style="color:#ef4444;">●</span> Cancelled {{ $pct($orderStatusSummary['cancelled']) }}%</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>