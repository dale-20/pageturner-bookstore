<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIReviewSummary extends Model
{
    use SoftDeletes;
    protected $table = 'ai_review_summaries';

    protected $fillable = [
        'book_id',
        'summary',
        'overall_sentiment',
        'sentiment_score',
        'reviews_analyzed',
        'provider_used',
        'status',
        'generated_at',
        'sentiment_breakdown',
    ];

    protected $casts = [
        'sentiment_breakdown' => 'array',
        'sentiment_score'     => 'float',
        'reviews_analyzed'    => 'integer',
        'generated_at'        => 'datetime',
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helpers
    public function getSentimentBadgeColorAttribute(): string
    {
        return match($this->overall_sentiment) {
            'positive' => 'green',
            'negative' => 'red',
            'neutral'  => 'yellow',
            default    => 'gray',
        };
    }

    public function getSentimentPercentageAttribute(): int
    {
        return (int) ($this->sentiment_score * 100);
    }

    public function isFresh(): bool
    {
        return $this->generated_at
            && $this->generated_at->greaterThanOrEqualTo(now()->subDays(7));
    }
}