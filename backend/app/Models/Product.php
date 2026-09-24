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

    /**
     * Standard sizes offered for size-less fashion products (products whose
     * seller did not define size variants). Shared with the cart validation.
     */
    public const STANDARD_SIZES = ['S', 'M', 'L', 'XL'];

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

    protected $appends = ['size_options'];

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

    /**
     * Standard size options offered by the storefront when a product has no
     * seller-defined variants. Size-less stock (the single default variant)
     * is shared by all of them, so they are only offered for fashion products.
     * Serialized as `size_options` on every product API response.
     */
    public function getSizeOptionsAttribute(): ?array
    {
        if ($this->relationLoaded('variants')) {
            $hasRealVariants = $this->variants->contains(
                fn ($variant) => $variant->relationLoaded('attributeValues')
                    ? $variant->attributeValues->isNotEmpty()
                    : $variant->attributeValues()->exists()
            );
        } else {
            $hasRealVariants = $this->variants()
                ->whereHas('attributeValues')
                ->exists();
        }

        if ($hasRealVariants || ! $this->category?->isFashion()) {
            return null;
        }

        return self::STANDARD_SIZES;
    }
}
