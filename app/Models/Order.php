<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Order extends Model implements Auditable
{
    //
    use HasFactory, SoftDeletes;
    use AuditableTrait;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
    ];

    protected $auditInclude = [
        'status',
        'total_amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function generateTags(): array
    {
        return ['order_status_change'];
    }
}
