<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_order_id',
        'raised_by',
        'reason',
        'status',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function raiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInReview(): bool
    {
        return $this->status === 'in_review';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
