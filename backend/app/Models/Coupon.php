<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'code',
        'type',
        'value',
        'min_spend',
        'usage_limit',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_spend' => 'decimal:2',
            'usage_limit' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isPlatformWide(): bool
    {
        return $this->store_id === null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && (!$this->starts_at || $this->starts_at->isPast())
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    public function isUsableBy(User $user, float $subtotal): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        if ($subtotal < $this->min_spend) {
            return false;
        }
        if ($this->usage_limit && $this->usages()->count() >= $this->usage_limit) {
            return false;
        }
        if ($this->usages()->where('user_id', $user->id)->exists()) {
            return false;
        }
        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $subtotal): float
    {
        return match($this->type) {
            'percentage' => $subtotal * ($this->value / 100),
            'fixed' => min($this->value, $subtotal),
            default => 0,
        };
    }
}
