<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action',
        'subject_id',
        'subject_type',
        'changes',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an audit action
     */
    public static function log(
        User $actor,
        string $action,
        ?Model $subject = null,
        ?array $changes = null,
        ?string $ipAddress = null
    ): self {
        return static::create([
            'actor_id' => $actor->id,
            'action' => $action,
            'subject_id' => $subject?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'changes' => $changes,
            'ip_address' => $ipAddress,
            'created_at' => now(),
        ]);
    }
}
