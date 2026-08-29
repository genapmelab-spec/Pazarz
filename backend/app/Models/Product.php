<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'status',
        'weight_grams',
        'rating_avg',
        'rating_count',
        'sold_count',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'weight_grams' => 'integer',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get the effective price for a variant
     */
    public function getEffectivePrice(ProductVariant $variant): float
    {
        return $variant->price ?? $this->base_price;
    }

    /**
     * Get variant label from attribute values
     */
    public function getVariantLabel(ProductVariant $variant): string
    {
        return $variant->attributeValues
            ->map(fn($av) => $av->value)
            ->implode(' / ');
    }
}
