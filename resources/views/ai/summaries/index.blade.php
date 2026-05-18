@extends('layouts.admin-layout')

@section('content')
<div class="row g-4">

    {{-- Page Header --}}
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <span class="d-flex align-items-center justify-content-center rounded-circle"
                          style="width:36px;height:36px;background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="feather feather-cpu text-white" style="font-size:.9rem;"></i>
                    </span>
                    AI Review Summaries
                </h4>
                <p class="text-muted mb-0 ms-1">Manage AI-generated book review summaries and sentiment analysis.</p>
            </div>
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather feather-arrow-left me-1"></i> Back to Books
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="feather feather-cpu text-white"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1">{{ $stats['total'] }}</div>
                        <div class="text-muted small mt-1">Total Summaries</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="feather feather-check-circle text-white"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1">{{ $stats['completed'] }}</div>
                        <div class="text-muted small mt-1">Completed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="feather feather-clock text-white"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1">{{ $stats['pending'] }}</div>
                        <div class="text-muted small mt-1">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#ef4444,#dc2626);">
                        <i class="feather feather-alert-circle text-white"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1">{{ $stats['failed'] }}</div>
                        <div class="text-muted small mt-1">Failed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sentiment Distribution --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-4">Overall Sentiment Distribution</h6>
                @php $total = max($stats['completed'], 1); @endphp
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                             style="background:#f0fdf4; border:1px solid #bbf7d0;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:#22c55e;">
                                <i class="feather feather-smile text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-success">Positive</span>
                                    <span class="fw-bold text-success fs-5">{{ $stats['positive'] }}</span>
                                </div>
                                <div class="progress" style="height:6px;background:#dcfce7;">
                                    <div class="progress-bar bg-success"
                                         style="width:{{ round($stats['positive']/$total*100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($stats['positive']/$total*100) }}% of completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                             style="background:#fffbeb; border:1px solid #fde68a;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:#f59e0b;">
                                <i class="feather feather-meh text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-warning">Neutral</span>
                                    <span class="fw-bold text-warning fs-5">{{ $stats['neutral'] }}</span>
                                </div>
                                <div class="progress" style="height:6px;background:#fef3c7;">
                                    <div class="progress-bar bg-warning"
                                         style="width:{{ round($stats['neutral']/$total*100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($stats['neutral']/$total*100) }}% of completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                             style="background:#fef2f2; border:1px solid #fecaca;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:#ef4444;">
                                <i class="feather feather-frown text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-danger">Negative</span>
                                    <span class="fw-bold text-danger fs-5">{{ $stats['negative'] }}</span>
                                </div>
                                <div class="progress" style="height:6px;background:#fee2e2;">
                                    <div class="progress-bar bg-danger"
                                         style="width:{{ round($stats['negative']/$total*100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($stats['negative']/$total*100) }}% of completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summaries Table --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h6 class="fw-bold mb-0">All Summaries</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="summariesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Book</th>
                                <th>Sentiment</th>
                                <th>Confidence</th>
                                <th>Reviews</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Generated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summaries as $summary)
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ Str::limit($summary->book->title ?? 'Unknown', 40) }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">ID #{{ $summary->book_id }}</div>
                                </td>
                                <td>
                                    @php
                                        $sc = match($summary->overall_sentiment) {
                                            'positive' => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#16a34a','dot'=>'#22c55e'],
                                            'negative' => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626','dot'=>'#ef4444'],
                                            default    => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#d97706','dot'=>'#f59e0b'],
                                        };
                                    @endphp
                                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                          style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};color:{{ $sc['text'] }};">
                                        <span class="rounded-circle"
                                              style="width:6px;height:6px;background:{{ $sc['dot'] }};display:inline-block;"></span>
                                        {{ ucfirst($summary->overall_sentiment) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;width:60px;background:#f1f5f9;">
                                            <div class="progress-bar"
                                                 style="width:{{ $summary->sentiment_percentage }}%;background:{{ $sc['dot'] }};">
                                            </div>
                                        </div>
                                        <small class="text-dark fw-semibold">{{ $summary->sentiment_percentage }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $summary->reviews_analyzed }}</span>
                                    <span class="text-muted small"> reviews</span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded small fw-semibold"
                                          style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;">
                                        {{ ucfirst($summary->provider_used) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $st = match($summary->status) {
                                            'completed' => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#16a34a'],
                                            'pending'   => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#d97706'],
                                            'failed'    => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626'],
                                            default     => ['bg'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#64748b'],
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded small fw-semibold"
                                          style="background:{{ $st['bg'] }};border:1px solid {{ $st['border'] }};color:{{ $st['text'] }};">
                                        {{ ucfirst($summary->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $summary->generated_at?->diffForHumans() ?? '—' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.ai.summaries.show', $summary->book_id) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="feather feather-eye"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.ai.summaries.generate', $summary->book_id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Regenerate">
                                                <i class="feather feather-refresh-cw"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.ai.summaries.destroy', $summary->book_id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    onclick="return confirm('Delete this summary?')">
                                                <i class="feather feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="feather feather-cpu d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                                    No summaries generated yet. Go to a book page and generate one.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $summaries->links() }}
            </div>
        </div>
    </div>

    {{-- Provider Usage Stats --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="feather feather-bar-chart-2 text-primary"></i>
                    Provider Usage Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Provider</th>
                                <th>Total Calls</th>
                                <th>Tokens Used</th>
                                <th>Est. Cost</th>
                                <th>Success Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($providerStats as $stat)
                            @php
                                $providerColors = [
                                    'gemini'      => ['bg'=>'#eff6ff','border'=>'#bfdbfe','text'=>'#1d4ed8'],
                                    'huggingface' => ['bg'=>'#fdf4ff','border'=>'#e9d5ff','text'=>'#7c3aed'],
                                    'ollama'      => ['bg'=>'#f0fdf4','border'=>'#bbf7d0','text'=>'#15803d'],
                                ];
                                $pc = $providerColors[$stat->provider] ?? ['bg'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#475569'];
                            @endphp
                            <tr>
                                <td>
                                    <span class="px-2 py-1 rounded small fw-semibold"
                                          style="background:{{ $pc['bg'] }};border:1px solid {{ $pc['border'] }};color:{{ $pc['text'] }};">
                                        {{ ucfirst($stat->provider) }}
                                    </span>
                                </td>
                                <td><span class="fw-semibold">{{ number_format($stat->total) }}</span></td>
                                <td><span class="fw-semibold">{{ number_format($stat->tokens) }}</span></td>
                                <td>
                                    <span class="fw-semibold text-success">$0.00</span>
                                    <small class="text-muted ms-1">(free tier)</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;max-width:80px;">
                                            <div class="progress-bar bg-success" style="width:100%"></div>
                                        </div>
                                        <small class="text-success fw-semibold">Free</small>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No usage logged yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#summariesTable').DataTable({
            pageLength: 15,
            ordering: true,
            searching: true,
            order: [[6, 'desc']]
        });
    });
</script>
@endsection