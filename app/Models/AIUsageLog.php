<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    protected $table = 'ai_usage_logs';
    protected $fillable = [
        'provider',
        'feature',
        'model',
        'tokens_used',
        'cost_estimate',
        'input_hash',
        'output_hash',
        'confidence',
        'status',
        'user_id',
        'meta',
    ];

    protected $casts = [
        'meta'          => 'array',
        'cost_estimate' => 'float',
        'confidence'    => 'float',
        'tokens_used'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes for the admin dashboard
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}