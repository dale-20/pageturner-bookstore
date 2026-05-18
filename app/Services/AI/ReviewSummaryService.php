<?php

namespace App\Services\AI;

use App\Models\AIReviewSummary;
use App\Models\Book;
use Illuminate\Support\Facades\Log;

class ReviewSummaryService
{
    public function __construct(
        protected AIServiceManager $ai
    ) {
    }

    /**
     * Generate a summary and sentiment analysis for a book's reviews.
     */
    public function summarizeForBook(int $bookId): AIReviewSummary
    {
        $book = Book::with('reviews')->findOrFail($bookId);
        $reviews = $book->reviews;

        if ($reviews->isEmpty()) {
            throw new \RuntimeException("Book #{$bookId} has no reviews to summarize.");
        }

        $summary = AIReviewSummary::updateOrCreate(
            ['book_id' => $bookId],
            [
                'status' => 'pending',
                'reviews_analyzed' => $reviews->count(),
                'summary' => '',
                'overall_sentiment' => 'neutral',
                'sentiment_score' => 0.0,
                'provider_used' => 'pending',
            ]
        );

        try {
            // Step 1: Build prompt

            $reviewText = $reviews
                ->take(20)
                ->map(fn($r) => "- [{$r->rating}/5] {$r->comment}")
                ->join("\n");

            $prompt = "Here are customer reviews for a book:\n\n{$reviewText}\n\nWrite a short 2-3 sentence summary of what customers think about this book:";

            // Step 2: Generate summary via AI
            $generatedSummary = $this->ai->generate($prompt, 'review_summary');

            // Step 3: Calculate overall sentiment from average rating
            $avgRating = $reviews->take(20)->avg('rating');

            if ($avgRating >= 4.0) {
                $sentiment = ['label' => 'positive', 'score' => round($avgRating / 5, 2)];
            } elseif ($avgRating >= 3.0) {
                $sentiment = ['label' => 'positive', 'score' => round($avgRating / 5, 2)];
            } elseif ($avgRating >= 2.0) {
                $sentiment = ['label' => 'negative', 'score' => round(1 - ($avgRating / 5), 2)];
            } else {
                $sentiment = ['label' => 'negative', 'score' => round(1 - ($avgRating / 5), 2)];
            }

            // Override to neutral if ratings are very mixed (high std deviation)
            $ratings = $reviews->take(20)->pluck('rating');
            $mean = $ratings->avg();
            $variance = $ratings->map(fn($r) => pow($r - $mean, 2))->avg();
            $stdDev = sqrt($variance);

            if ($stdDev > 1.5) {
                $sentiment = ['label' => 'neutral', 'score' => round($avgRating / 5, 2)];
            }

            // Step 4: Build sentiment breakdown per review using rating
            $breakdown = $reviews->take(20)->map(function ($review) {
                if ($review->rating >= 4) {
                    $label = 'positive';
                    $score = 0.90;
                } elseif ($review->rating <= 2) {
                    $label = 'negative';
                    $score = 0.90;
                } else {
                    $label = 'neutral';
                    $score = 0.50;
                }

                return [
                    'review_id' => $review->id,
                    'rating' => $review->rating,
                    'label' => $label,
                    'score' => $score,
                ];
            })->toArray();

            // Step 5: Save completed summary
            $summary->update([
                'summary' => $generatedSummary,
                'overall_sentiment' => $sentiment['label'],
                'sentiment_score' => $sentiment['score'],
                'sentiment_breakdown' => $breakdown,
                'provider_used' => $this->ai->lastUsedProvider, // ← fix this
                'status' => 'completed',
                'generated_at' => now(),
            ]);

        } catch (\Exception $e) {
            $summary->update(['status' => 'failed']);
            Log::error("ReviewSummaryService failed for book #{$bookId}: " . $e->getMessage());
            throw $e;
        }

        return $summary->fresh();
    }

    /**
     * Get existing summary for a book or return null.
     */
    public function getSummaryForBook(int $bookId): ?AIReviewSummary
    {
        return AIReviewSummary::where('book_id', $bookId)
            ->where('status', 'completed')
            ->first();
    }

    /**
     * Check if a book already has a fresh summary (generated within 7 days).
     */
    public function hasFreshSummary(int $bookId): bool
    {
        return AIReviewSummary::where('book_id', $bookId)
            ->where('status', 'completed')
            ->where('generated_at', '>=', now()->subDays(7))
            ->exists();
    }
}