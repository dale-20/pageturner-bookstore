@extends('layouts.admin-layout')

@section('content')
<div class="row g-4">

    {{-- Page Header --}}
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-1">{{ Str::limit($book->title, 60) }}</h4>
                <p class="text-muted mb-0">
                    by {{ $book->author }}
                    &middot; ISBN: {{ $book->isbn }}
                    &middot; <span class="text-primary">AI Summary</span>
                </p>
            </div>
            <a href="{{ route('admin.ai.summaries.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather feather-arrow-left me-1"></i> Back to Summaries
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if(session('info'))
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show mb-0" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if($summary)

        @php
            $sentiment = $summary->overall_sentiment;
            $score     = $summary->sentiment_percentage;
            $color     = match($sentiment) {
                'positive' => 'success',
                'negative' => 'danger',
                default    => 'warning'
            };
            $icon = match($sentiment) {
                'positive' => 'smile',
                'negative' => 'frown',
                default    => 'meh'
            };
            $hex = match($sentiment) {
                'positive' => '#22c55e',
                'negative' => '#ef4444',
                default    => '#f59e0b'
            };
            $hexDark = match($sentiment) {
                'positive' => '#16a34a',
                'negative' => '#dc2626',
                default    => '#d97706'
            };
            $lightBg = match($sentiment) {
                'positive' => '#f0fdf4',
                'negative' => '#fef2f2',
                default    => '#fffbeb'
            };
            $lightBorder = match($sentiment) {
                'positive' => '#bbf7d0',
                'negative' => '#fecaca',
                default    => '#fde68a'
            };
        @endphp

        {{-- Sentiment Overview --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">

                    {{-- SVG Ring --}}
                    <div class="position-relative d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:120px;height:120px;">
                        <svg width="120" height="120" viewBox="0 0 120 120" style="transform:rotate(-90deg);">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                            <circle cx="60" cy="60" r="50" fill="none"
                                    stroke="{{ $hex }}" stroke-width="10"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ round($score * 3.14) }} 314"
                                    style="transition:stroke-dasharray 1s ease;"/>
                        </svg>
                        <div class="position-absolute text-center">
                            <i class="feather feather-{{ $icon }}" style="font-size:1.5rem;color:{{ $hex }};"></i>
                            <div class="fw-bold" style="color:{{ $hex }};font-size:.85rem;">{{ $score }}%</div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-1" style="color:{{ $hexDark }};">
                        {{ ucfirst($sentiment) }}
                    </h4>
                    <p class="text-muted mb-3">Overall Sentiment</p>

                    {{-- Stats Row --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:{{ $lightBg }};border:1px solid {{ $lightBorder }};">
                                <div class="fw-bold fs-5" style="color:{{ $hexDark }};">{{ $summary->reviews_analyzed }}</div>
                                <div class="text-muted small">Reviews</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                <div class="fw-bold fs-5 text-primary">{{ ucfirst($summary->provider_used) }}</div>
                                <div class="text-muted small">AI Provider</div>
                            </div>
                        </div>
                    </div>

                    {{-- Status & Date --}}
                    <div class="mb-3">
                        @php
                            $st = match($summary->status) {
                                'completed' => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#16a34a'],
                                'pending'   => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#d97706'],
                                'failed'    => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626'],
                                default     => ['bg'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#64748b'],
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-pill small fw-semibold"
                              style="background:{{ $st['bg'] }};border:1px solid {{ $st['border'] }};color:{{ $st['text'] }};">
                            {{ ucfirst($summary->status) }}
                        </span>
                        @if($summary->generated_at)
                            <div class="text-muted small mt-2">
                                <i class="feather feather-clock me-1" style="font-size:.75rem;"></i>
                                Generated {{ $summary->generated_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="generateNowBtn"
                                data-book-id="{{ $book->id }}">
                            <i class="feather feather-zap me-1"></i> Generate Now (Instant)
                        </button>
                        <form method="POST" action="{{ route('admin.ai.summaries.generate', $book->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                <i class="feather feather-clock me-1"></i> Queue Regeneration
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.ai.summaries.destroy', $book->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                    onclick="return confirm('Delete this summary?')">
                                <i class="feather feather-trash-2 me-1"></i> Delete Summary
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- AI Summary Text --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span class="d-flex align-items-center justify-content-center rounded-circle"
                                  style="width:28px;height:28px;background:linear-gradient(135deg,#667eea,#764ba2);">
                                <i class="feather feather-cpu text-white" style="font-size:.7rem;"></i>
                            </span>
                            AI-Generated Summary
                        </h6>
                        <span class="px-2 py-1 rounded small fw-semibold"
                              style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;">
                            ✦ AI Generated
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="p-3 rounded-3 mb-4"
                         style="background:#f8fafc;border-left:4px solid {{ $hex }};border:1px solid #e2e8f0;border-left:4px solid {{ $hex }};">
                        <p class="text-dark lh-lg mb-0">{{ $summary->summary }}</p>
                    </div>

                    {{-- Sentiment Breakdown Preview --}}
                    @php
                        $breakdown     = $summary->sentiment_breakdown ?? [];
                        $posCount      = collect($breakdown)->where('label','positive')->count();
                        $negCount      = collect($breakdown)->where('label','negative')->count();
                        $neuCount      = collect($breakdown)->where('label','neutral')->count();
                        $bdTotal       = max(count($breakdown), 1);
                    @endphp
                    @if(count($breakdown) > 0)
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="text-center p-3 rounded-3"
                                 style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <div class="fw-bold fs-4" style="color:#16a34a;">{{ $posCount }}</div>
                                <div class="small" style="color:#15803d;">Positive</div>
                                <div class="small text-muted">{{ round($posCount/$bdTotal*100) }}%</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 rounded-3"
                                 style="background:#fffbeb;border:1px solid #fde68a;">
                                <div class="fw-bold fs-4" style="color:#d97706;">{{ $neuCount }}</div>
                                <div class="small" style="color:#b45309;">Neutral</div>
                                <div class="small text-muted">{{ round($neuCount/$bdTotal*100) }}%</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 rounded-3"
                                 style="background:#fef2f2;border:1px solid #fecaca;">
                                <div class="fw-bold fs-4" style="color:#dc2626;">{{ $negCount }}</div>
                                <div class="small" style="color:#b91c1c;">Negative</div>
                                <div class="small text-muted">{{ round($negCount/$bdTotal*100) }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Stacked bar --}}
                    <div class="d-flex rounded-pill overflow-hidden mb-1" style="height:8px;gap:2px;">
                        @if($posCount > 0)
                        <div class="bg-success rounded-pill"
                             style="width:{{ round($posCount/$bdTotal*100) }}%;transition:width .8s ease;"></div>
                        @endif
                        @if($neuCount > 0)
                        <div class="bg-warning rounded-pill"
                             style="width:{{ round($neuCount/$bdTotal*100) }}%;transition:width .8s ease;"></div>
                        @endif
                        @if($negCount > 0)
                        <div class="bg-danger rounded-pill"
                             style="width:{{ round($negCount/$bdTotal*100) }}%;transition:width .8s ease;"></div>
                        @endif
                    </div>
                    <small class="text-muted">Sentiment distribution across {{ count($breakdown) }} reviews</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Per-Review Sentiment Breakdown --}}
        @if($summary->sentiment_breakdown)
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Per-Review Sentiment Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Review ID</th>
                                    <th>Rating</th>
                                    <th>Sentiment</th>
                                    <th>Confidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary->sentiment_breakdown as $index => $item)
                                @php
                                    $bc = match($item['label']) {
                                        'positive' => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#16a34a','bar'=>'#22c55e'],
                                        'negative' => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626','bar'=>'#ef4444'],
                                        default    => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#d97706','bar'=>'#f59e0b'],
                                    };
                                @endphp
                                <tr>
                                    <td><small class="text-muted">{{ $index + 1 }}</small></td>
                                    <td><small class="text-muted">#{{ $item['review_id'] }}</small></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="feather feather-star {{ $i <= $item['rating'] ? 'text-warning' : 'text-muted' }}"
                                               style="font-size:.75rem;"></i>
                                        @endfor
                                        </div>
                                    </td>
                                    <td>
                                        <span class="px-2 py-1 rounded small fw-semibold"
                                              style="background:{{ $bc['bg'] }};border:1px solid {{ $bc['border'] }};color:{{ $bc['text'] }};">
                                            {{ ucfirst($item['label']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress" style="height:6px;width:80px;background:#f1f5f9;">
                                                <div class="progress-bar"
                                                     style="width:{{ round($item['score'] * 100) }}%;background:{{ $bc['bar'] }};"></div>
                                            </div>
                                            <small class="text-dark fw-semibold">{{ round($item['score'] * 100) }}%</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @else

        {{-- No Summary Yet --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                         style="width:72px;height:72px;background:linear-gradient(135deg,#667eea22,#764ba222);">
                        <i class="feather feather-cpu text-primary" style="font-size:1.8rem;"></i>
                    </div>
                    <h5 class="fw-bold mt-2">No Summary Generated Yet</h5>
                    <p class="text-muted mb-4">
                        This book has no AI summary. Generate one now or queue it for background processing.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <form method="POST" action="{{ route('admin.ai.summaries.generate', $book->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success">
                                <i class="feather feather-clock me-1"></i> Queue Generation
                            </button>
                        </form>
                        <button type="button" class="btn btn-primary" id="generateNowBtn"
                                data-book-id="{{ $book->id }}">
                            <i class="feather feather-zap me-1"></i> Generate Now (Instant)
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endif

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('generateNowBtn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        const bookId = this.dataset.bookId;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';

        fetch(`/api/books/${bookId}/ai-summary/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Generation failed: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="feather feather-zap me-1"></i> Generate Now (Instant)';
            }
        })
        .catch(err => {
            alert('Request failed. Check console for details.');
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<i class="feather feather-zap me-1"></i> Generate Now (Instant)';
        });
    });
});
</script>
@endsection