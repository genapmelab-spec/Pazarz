<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'user_id',
        'product_id',
        'rating',
        'comment',
        'seller_reply',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function isVisible(): bool
    {
        return $this->status === 'visible';
    }

    public function isHidden(): bool
    {
        return $this->status === 'hidden';
    }

    public function isFlagged(): bool
    {
        return $this->status === 'flagged';
    }
}
