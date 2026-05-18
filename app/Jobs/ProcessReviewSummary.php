<?php

namespace App\Jobs;

use App\Models\AIReviewSummary;
use App\Services\AI\ReviewSummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReviewSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry up to 3 times if it fails
    public int $tries = 3;

    // Timeout after 120 seconds
    public int $timeout = 120;

    // Wait 60 seconds between retries
    public int $backoff = 60;

    public function __construct(
        public readonly int $bookId
    ) {}

    public function handle(ReviewSummaryService $service): void
    {
        Log::info("ProcessReviewSummary: starting for book #{$this->bookId}");

        try {
            // Skip if a fresh summary already exists
            if ($service->hasFreshSummary($this->bookId)) {
                Log::info("ProcessReviewSummary: fresh summary exists for book #{$this->bookId}, skipping.");
                return;
            }

            $summary = $service->summarizeForBook($this->bookId);

            Log::info("ProcessReviewSummary: completed for book #{$this->bookId}", [
                'sentiment' => $summary->overall_sentiment,
                'provider'  => $summary->provider_used,
            ]);

        } catch (\Exception $e) {
            Log::error("ProcessReviewSummary: failed for book #{$this->bookId}: " . $e->getMessage());

            // Mark as failed in DB so admin can see it
            AIReviewSummary::where('book_id', $this->bookId)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);

            // Re-throw so the queue retries
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessReviewSummary: all retries exhausted for book #{$this->bookId}", [
            'error' => $exception->getMessage(),
        ]);

        AIReviewSummary::where('book_id', $this->bookId)
            ->update(['status' => 'failed']);
    }
}