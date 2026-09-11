<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_id',
        'status',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class);
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlist(): HasOne
    {
        return $this->hasOne(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function isCustomer(): bool
    {
        return $this->role_id === 'customer';
    }

    public function isSeller(): bool
    {
        return $this->role_id === 'seller';
    }

    public function isAdmin(): bool
    {
        return $this->role_id === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Where a logged-in user should land after login / when hitting the root.
     * Sellers and admins go to their dashboards; everyone else goes to the storefront.
     */
    public function dashboardUrl(): string
    {
        if ($this->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($this->isSeller()) {
            $seller = $this->seller;

            // Not approved yet — show the pending/rejected page instead of a 403
            if ($seller?->isPending()) {
                return route('seller.pending');
            }

            if ($seller && $seller->verification_status === 'rejected') {
                return route('seller.rejected');
            }

            return route('seller.dashboard');
        }

        return config('app.frontend_url', 'http://localhost:5173');
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->isCustomer()) {
                Cart::create(['user_id' => $user->id]);
                Wishlist::create(['user_id' => $user->id]);
            }
        });
    }
}
