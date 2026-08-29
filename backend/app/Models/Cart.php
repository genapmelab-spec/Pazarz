<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get cart items grouped by store
     */
    public function getGroupedByStoreAttribute(): \Illuminate\Support\Collection
    {
        return $this->items
            ->filter(fn($item) => $item->relationLoaded('variant') && $item->variant && $item->variant->relationLoaded('product'))
            ->groupBy(fn($item) => $item->variant->product->store_id)
            ->map(fn($items, $storeId) => [
                'store' => $items->first()->variant->product->store,
                'items' => $items,
                'subtotal' => $items->sum(fn($item) => $item->price_snapshot * $item->quantity),
            ]);
    }

    /**
     * Get cart subtotal
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->price_snapshot * $item->quantity);
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
