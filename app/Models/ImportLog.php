<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'model_type',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'failures',
        'user_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'failures' => 'array',
        'total_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
        'completed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Model type constants
    const MODEL_BOOK = 'Book';
    const MODEL_USER = 'User';
    const MODEL_ORDER = 'Order';

    /**
     * Get the user who performed the import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if import is still processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if import is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if import failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        
        return round(($this->successful_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Get failure rate percentage
     */
    public function getFailureRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        
        return round(($this->failed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Check if there are any failures
     */
    public function hasFailures(): bool
    {
        return !empty($this->failures) && $this->failed_rows > 0;
    }

    /**
     * Scope for completed imports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for processing imports
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for failed imports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for imports by model type
     */
    public function scopeOfType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope for recent imports
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}