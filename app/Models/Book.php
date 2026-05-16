<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Book extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;
    use Searchable;

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

    protected $auditThreshold = 0;

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeInCategory(Builder $query, mixed $categoryId): Builder
    {
        return $query->when(
            $categoryId,
            fn(Builder $q) => $q->where('category_id', $categoryId)
        );
    }

    /**
     * Full-text search scope.
     *
     * Uses the PostgreSQL GIN index (search_vector) for strings >= 3 chars,
     * which is orders of magnitude faster than LIKE on 1M rows.
     * Falls back to LIKE for very short strings that tsquery would reject.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search) {
            if (strlen($search) >= 3) {
                $query->whereRaw(
                    "search_vector @@ plainto_tsquery('english', ?)",
                    [$search]
                );
            } else {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            }
        });
    }

    // -------------------------------------------------------------------------
    // Scout — Meilisearch
    // -------------------------------------------------------------------------

    /**
     * Fields sent to Meilisearch.
     * Only uses columns that actually exist in the books table.
     * description is excluded to keep the index lean on 1M records.
     */
    public function toSearchableArray(): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'author'         => $this->author,
            'isbn'           => $this->isbn,
            'price'          => (float) $this->price,
            'stock_quantity' => (int) $this->stock_quantity,
            'category_id'    => $this->category_id,
        ];
    }

    /**
     * All books are searchable — is_active does not exist in this table.
     * Stock-based filtering is handled at query time via Scout options.
     */
    public function shouldBeSearchable(): bool
    {
        return true;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Average rating accessor.
     * Do NOT call this in a loop — use withAvg('reviews','rating') instead.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    // -------------------------------------------------------------------------
    // Auditing
    // -------------------------------------------------------------------------

    public function shouldAudit($event): bool
    {
        if ($event === 'updated' && isset($this->getDirty()['price'])) {
            $oldPrice = (float) $this->getOriginal('price');
            $newPrice = (float) $this->getAttribute('price');

            if ($oldPrice == 0) {
                return true;
            }

            $changePercent = abs(($newPrice - $oldPrice) / $oldPrice) * 100;

            return $changePercent > 10;
        }

        return true;
    }
}