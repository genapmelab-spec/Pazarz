<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'description',
        'ip_address',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}