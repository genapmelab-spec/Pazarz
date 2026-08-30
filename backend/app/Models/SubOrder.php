<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'store_id',
        'subtotal',
        'shipping_cost',
        'status',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Valid status transitions
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return match($this->status) {
            'pending' => in_array($newStatus, ['confirmed', 'cancelled']),
            'confirmed' => in_array($newStatus, ['processing', 'shipped', 'cancelled']),
            'processing' => in_array($newStatus, ['shipped', 'cancelled']),
            'shipped' => in_array($newStatus, ['completed']),
            default => false,
        };
    }
}
