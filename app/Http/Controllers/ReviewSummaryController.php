<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReviewSummary;
use App\Models\AIReviewSummary;
use App\Models\AIUsageLog;
use App\Models\Book;
use App\Services\AI\ReviewSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewSummaryController extends Controller
{
    public function __construct(
        protected ReviewSummaryService $service
    ) {
    }

    /**
     * Admin dashboard — list all summaries.
     */
    public function index()
    {
        $summaries = AIReviewSummary::with('book')
            ->orderByDesc('generated_at')
            ->paginate(15);

        $stats = [
            'total' => AIReviewSummary::count(),
            'completed' => AIReviewSummary::completed()->count(),
            'pending' => AIReviewSummary::pending()->count(),
            'failed' => AIReviewSummary::failed()->count(),
            'positive' => AIReviewSummary::completed()->where('overall_sentiment', 'positive')->count(),
            'negative' => AIReviewSummary::completed()->where('overall_sentiment', 'negative')->count(),
            'neutral' => AIReviewSummary::completed()->where('overall_sentiment', 'neutral')->count(),
        ];

        $usageLogs = AIUsageLog::orderByDesc('created_at')->take(20)->get();

        $providerStats = AIUsageLog::selectRaw('provider, count(*) as total, sum(tokens_used) as tokens')
            ->groupBy('provider')
            ->get();

        return view('ai.summaries.index', compact('summaries', 'stats', 'usageLogs', 'providerStats'));
    }

    /**
     * Show a single book's AI summary.
     */
    public function show(int $bookId)
    {
        $book = Book::findOrFail($bookId);
        $summary = $this->service->getSummaryForBook($bookId);

        return view('ai.summaries.show', compact('book', 'summary'));
    }

    /**
     * Dispatch a queue job to generate summary for a book.
     */
    public function generate(Request $request, int $bookId)
    {
        $book = Book::findOrFail($bookId);

        // Check if already processing
        $existing = AIReviewSummary::where('book_id', $bookId)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('info', 'A summary is already being generated for this book.');
        }

        // Dispatch to queue
        ProcessReviewSummary::dispatch($bookId)->onQueue('ai-tasks');

        Log::info("ReviewSummaryController: dispatched job for book #{$bookId}");

        return back()->with('success', 'Summary generation has been queued. Refresh in a moment.');
    }

    /**
     * Generate summary immediately (synchronous — for testing).
     */
    public function generateNow(int $bookId)
    {
        try {
            $book = Book::findOrFail($bookId);
            $summary = $this->service->summarizeForBook($bookId);

            return response()->json([
                'success' => true,
                'summary' => $summary->summary,
                'sentiment' => $summary->overall_sentiment,
                'score' => $summary->sentiment_percentage,
                'provider' => $summary->provider_used,
                'reviews' => $summary->reviews_analyzed,
            ]);

        } catch (\Exception $e) {
            Log::error('generateNow failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        Log::info('AI Ollama config in web request:', config('ai.providers.ollama') ?? ['NULL - not found']);
        Log::info('AI fallback chain:', config('ai.fallback_chain') ?? ['NULL']);
    }

    /**
     * Delete a summary so it can be regenerated.
     */
    public function destroy(int $bookId)
    {
        AIReviewSummary::where('book_id', $bookId)->delete();

        return back()->with('success', 'Summary deleted. You can now regenerate it.');
    }
}