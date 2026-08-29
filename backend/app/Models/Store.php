<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'name',
        'slug',
        'logo_url',
        'banner_url',
        'description',
        'rating_avg',
        'rating_count',
        'status',
        'address_id',
    ];

    protected function casts(): array
    {
        return [
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(SellerFollower::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
