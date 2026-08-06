<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'description',
        'status',
        'base_price',
        'rating_avg',
        'review_count',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'review_count' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}