<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'image_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the effective price (variant price or product base price)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price ?? $this->product->base_price;
    }

    /**
     * Get available stock
     */
    public function getAvailableStockAttribute(): int
    {
        if (!$this->inventory) {
            return 0;
        }
        return $this->inventory->quantity - $this->inventory->reserved_quantity;
    }

    /**
     * Get variant label from attribute values
     */
    public function getLabelAttribute(): string
    {
        return $this->attributeValues
            ->map(fn($av) => $av->value)
            ->implode(' / ');
    }

    /**
     * Get product images as array (for cart/checkout display)
     */
    public function getProductImagesAttribute(): array
    {
        if ($this->relationLoaded('product')) {
            return $this->product->images->toArray();
        }
        return [];
    }
}
