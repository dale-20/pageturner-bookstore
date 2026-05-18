{{--
    AI Review Summary Widget
    Usage: @include('ai.summaries._widget', ['book' => $book])
--}}

@php
    $aiSummary = app(\App\Services\AI\ReviewSummaryService::class)->getSummaryForBook($book->id);
@endphp

<div class="card border-0 shadow-sm mt-4" id="ai-summary-widget">
    <div class="card-header border-0 pt-4 pb-0"
         style="background: linear-gradient(135deg, #667eea11 0%, #764ba211 100%);">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
                      style="width:32px;height:32px;">
                    <i class="feather feather-cpu text-primary" style="font-size:.9rem;"></i>
                </span>
                AI Review Summary
            </h6>
            <span class="badge border border-primary text-primary bg-primary bg-opacity-10"
                  style="font-size:.7rem; letter-spacing:.05em;">
                ✦ AI Generated
            </span>
        </div>
    </div>

    <div class="card-body pt-3">

        @if($aiSummary && $aiSummary->status === 'completed')

            @php
                $sentiment = $aiSummary->overall_sentiment;
                $score     = $aiSummary->sentiment_percentage;
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
                $gradientStart = match($sentiment) {
                    'positive' => '#22c55e',
                    'negative' => '#ef4444',
                    default    => '#f59e0b'
                };
                $gradientEnd = match($sentiment) {
                    'positive' => '#16a34a',
                    'negative' => '#dc2626',
                    default    => '#d97706'
                };

                // Sentiment breakdown counts
                $breakdown  = $aiSummary->sentiment_breakdown ?? [];
                $positiveCount = collect($breakdown)->where('label', 'positive')->count();
                $negativeCount = collect($breakdown)->where('label', 'negative')->count();
                $neutralCount  = collect($breakdown)->where('label', 'neutral')->count();
                $total         = max(count($breakdown), 1);
            @endphp

            {{-- Sentiment Hero --}}
            <div class="rounded-3 p-3 mb-3 d-flex align-items-center gap-3"
                 style="background: linear-gradient(135deg, {{ $gradientStart }}18, {{ $gradientEnd }}08);
                        border: 1px solid {{ $gradientStart }}30;">

                {{-- Circular Score --}}
                <div class="position-relative d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:72px; height:72px;">
                    <svg width="72" height="72" viewBox="0 0 72 72" style="transform:rotate(-90deg);">
                        <circle cx="36" cy="36" r="30" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle cx="36" cy="36" r="30" fill="none"
                                stroke="{{ $gradientStart }}" stroke-width="6"
                                stroke-linecap="round"
                                stroke-dasharray="{{ round($score * 1.885) }} 188.5"
                                style="transition: stroke-dasharray 1s ease;"/>
                    </svg>
                    <div class="position-absolute text-center">
                        <div class="fw-bold lh-1" style="font-size:.85rem; color:{{ $gradientStart }}">
                            {{ $score }}%
                        </div>
                    </div>
                </div>

                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="feather feather-{{ $icon }} text-{{ $color }}"></i>
                        <span class="fw-bold text-{{ $color }} fs-6">{{ ucfirst($sentiment) }} Sentiment</span>
                    </div>
                    <div class="text-muted small mb-2">Based on {{ $aiSummary->reviews_analyzed }} reviews</div>

                    {{-- Mini breakdown bar --}}
                    @if($total > 0)
                    <div class="d-flex rounded-pill overflow-hidden" style="height:6px; gap:1px;">
                        @if($positiveCount > 0)
                        <div class="bg-success" style="width:{{ round($positiveCount/$total*100) }}%; transition: width .8s ease;"></div>
                        @endif
                        @if($neutralCount > 0)
                        <div class="bg-warning" style="width:{{ round($neutralCount/$total*100) }}%; transition: width .8s ease;"></div>
                        @endif
                        @if($negativeCount > 0)
                        <div class="bg-danger" style="width:{{ round($negativeCount/$total*100) }}%; transition: width .8s ease;"></div>
                        @endif
                    </div>
                    <div class="d-flex gap-3 mt-1">
                        <small class="text-success">
                            <i class="feather feather-smile" style="font-size:.65rem;"></i>
                            {{ $positiveCount }} positive
                        </small>
                        <small class="text-warning">
                            <i class="feather feather-meh" style="font-size:.65rem;"></i>
                            {{ $neutralCount }} neutral
                        </small>
                        <small class="text-danger">
                            <i class="feather feather-frown" style="font-size:.65rem;"></i>
                            {{ $negativeCount }} negative
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Summary Text --}}
            <div class="rounded-3 p-3 mb-3"
                 style="background:#f8fafc; border-left: 3px solid {{ $gradientStart }};">
                <p class="text-dark lh-lg mb-0 small">{{ $aiSummary->summary }}</p>
            </div>

            {{-- Footer --}}
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted d-flex align-items-center gap-1">
                    <i class="feather feather-clock" style="font-size:.75rem;"></i>
                    {{ $aiSummary->generated_at?->diffForHumans() ?? 'recently' }}
                    &middot;
                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.65rem;">
                        via {{ ucfirst($aiSummary->provider_used) }}
                    </span>
                </small>
                <button type="button"
                        class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                        id="refreshSummaryBtn"
                        data-book-id="{{ $book->id }}">
                    <i class="feather feather-refresh-cw" style="font-size:.8rem;"></i>
                    Refresh
                </button>
            </div>

        @elseif($aiSummary && $aiSummary->status === 'pending')

            {{-- Processing State --}}
            <div class="text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-3"
                     style="width:56px;height:56px;">
                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                </div>
                <p class="fw-semibold mb-1">Generating AI Summary...</p>
                <p class="text-muted small mb-0">AI is analyzing all reviews for this book.</p>
            </div>

        @elseif($aiSummary && $aiSummary->status === 'failed')

            {{-- Failed State --}}
            <div class="text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 mb-3"
                     style="width:56px;height:56px;">
                    <i class="feather feather-alert-circle text-danger fs-5"></i>
                </div>
                <p class="fw-semibold mb-1">Generation Failed</p>
                <p class="text-muted small mb-3">Something went wrong. Try generating again.</p>
                <button type="button"
                        class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                        id="refreshSummaryBtn"
                        data-book-id="{{ $book->id }}">
                    <i class="feather feather-zap" style="font-size:.8rem;"></i>
                    Try Again
                </button>
            </div>

        @else

            {{-- No Summary State --}}
            <div class="text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width:56px;height:56px;
                            background: linear-gradient(135deg, #667eea22, #764ba222);">
                    <i class="feather feather-cpu text-primary fs-5"></i>
                </div>
                <p class="fw-semibold mb-1">No AI Summary Yet</p>
                <p class="text-muted small mb-3">
                    Generate an AI-powered summary of all reviews for this book.
                </p>
                <button type="button"
                        class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                        id="refreshSummaryBtn"
                        data-book-id="{{ $book->id }}">
                    <i class="feather feather-zap" style="font-size:.8rem;"></i>
                    Generate AI Summary
                </button>
            </div>

        @endif

    </div>
</div>

{{-- Widget Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('refreshSummaryBtn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        const bookId = this.dataset.bookId;
        const widget = document.getElementById('ai-summary-widget');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';

        widget.querySelector('.card-body').innerHTML = `
            <div class="text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-3"
                     style="width:56px;height:56px;">
                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                </div>
                <p class="fw-semibold mb-1">Analyzing Reviews...</p>
                <p class="text-muted small mb-0">AI is reading through all reviews. This may take a moment.</p>
            </div>
        `;

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
                widget.querySelector('.card-body').innerHTML = `
                    <div class="text-center py-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 mb-3"
                             style="width:56px;height:56px;">
                            <i class="feather feather-alert-circle text-danger fs-5"></i>
                        </div>
                        <p class="fw-semibold mb-1">Generation Failed</p>
                        <p class="text-muted small mb-2">${data.message}</p>
                        <button onclick="location.reload()" class="btn btn-sm btn-outline-danger">Try Again</button>
                    </div>
                `;
            }
        })
        .catch(err => {
            widget.querySelector('.card-body').innerHTML = `
                <div class="text-center py-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 mb-3"
                         style="width:56px;height:56px;">
                        <i class="feather feather-wifi-off text-danger fs-5"></i>
                    </div>
                    <p class="fw-semibold mb-1">Request Failed</p>
                    <p class="text-muted small mb-2">Could not reach the server. Please try again.</p>
                    <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary">Reload</button>
                </div>
            `;
            console.error(err);
        });
    });
});
</script>