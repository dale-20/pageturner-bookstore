<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model implements Auditable
{
    //
    use AuditableTrait;
    use HasFactory;
    protected $fillable = [
        'category_id',
        'title',
        'author',
        'isbn',
        'price',
        'stock_quantity',
        'description',
        'cover_image',
    ];
    // protected $auditInclude = [
    //     'category_id',
    //     'title',
    //     'author',
    //     'isbn',
    //     'price',
    //     'stock_quantity',
    //     'description',
    //     'cover_image',
    // ];

    protected $auditThreshold = 0;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    // Accessor for average rating
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
    public function shouldAudit($event): bool
    {
        if ($event === 'updated' && isset($this->getDirty()['price'])) {
            $oldPrice = $this->getOriginal('price');
            $newPrice = $this->getAttribute('price');
            $changePercent = abs(($newPrice - $oldPrice) / $oldPrice) * 100;

            return $changePercent > 10; // Only log price changes > 10%
        }

        return true;
    }
}
