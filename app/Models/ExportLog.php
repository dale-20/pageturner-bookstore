<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'model_type',
        'format',  // Changed back to 'format' to match migration
        'filters',
        'columns',
        'user_id',
        'status',
        'download_path',
        'rows_exported',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'rows_exported' => 'integer',
        'expires_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    // Format constants
    const FORMAT_CSV = 'csv';
    const FORMAT_XLSX = 'xlsx';
    const FORMAT_PDF = 'pdf';

    // Model type constants
    const MODEL_BOOK = 'Book';
    const MODEL_ORDER = 'Order';
    const MODEL_USER = 'User';

    /**
     * Get the user who performed the export
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if export is still processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if export is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if export has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if export is ready for download
     */
    public function isReadyForDownload(): bool
    {
        return $this->isCompleted() && 
               !$this->isExpired() && 
               $this->download_path && 
               file_exists(storage_path('app/public/' . $this->download_path));
    }

    /**
     * Get the full download URL
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->download_path && $this->isReadyForDownload()) {
            return asset('storage/' . $this->download_path);
        }
        
        return null;
    }

    /**
     * Get formatted file size
     */
    public function getFileSizeAttribute(): string
    {
        if (!$this->download_path) {
            return 'N/A';
        }
        
        $path = storage_path('app/public/' . $this->download_path);
        
        if (file_exists($path)) {
            $bytes = filesize($path);
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;
            
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            
            return round($bytes, 2) . ' ' . $units[$i];
        }
        
        return 'N/A';
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->expires_at);
    }

    /**
     * Mark export as expired
     */
    public function markAsExpired(): bool
    {
        return $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Delete the actual file when model is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($exportLog) {
            if ($exportLog->download_path && file_exists(storage_path('app/public/' . $exportLog->download_path))) {
                unlink(storage_path('app/public/' . $exportLog->download_path));
            }
        });
    }

    /**
     * Scope for completed exports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for processing exports
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for failed exports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for active exports (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /**
     * Scope for expired exports
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                     ->orWhere('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for exports by model type
     */
    public function scopeOfType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope for exports by format
     */
    public function scopeOfFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope for recent exports
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}