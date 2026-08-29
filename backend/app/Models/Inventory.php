<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get available stock (quantity - reserved)
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->available_stock <= $this->low_stock_threshold;
    }

    /**
     * Check if there's enough stock for a given quantity
     */
    public function hasStock(int $quantity): bool
    {
        return $this->available_stock >= $quantity;
    }

    /**
     * Reserve stock for checkout
     */
    public function reserve(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }
        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    /**
     * Release reserved stock (e.g., when order is cancelled)
     */
    public function release(int $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    /**
     * Deduct stock after successful payment
     */
    public function deduct(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }
        $this->decrement('quantity', $quantity);
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
        return true;
    }
}
