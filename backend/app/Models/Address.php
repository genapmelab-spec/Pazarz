<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'label',
        'recipient_name',
        'phone',
        'province',
        'city',
        'district',
        'postal_code',
        'full_address',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFormattedAddressAttribute(): string
    {
        return "{$this->full_address}, {$this->district}, {$this->city}, {$this->province} {$this->postal_code}";
    }
}
