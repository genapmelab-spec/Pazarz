<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'description',
        'verification_status',
        'verification_notes',
        'rating_avg',
        'commission_override_pct',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}