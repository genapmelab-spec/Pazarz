<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_order_id',
        'product_variant_id',
        'product_name_snapshot',
        'variant_label_snapshot',
        'price_snapshot',
        'quantity',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Check if this item has been reviewed
     */
    public function hasReview(): bool
    {
        return $this->review()->exists();
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }
}
