<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Everyone can browse products
    }

    public function view(User $user, Product $product): bool
    {
        return true; // Products are public
    }

    public function create(User $user): bool
    {
        return $user->hasRole('seller') && $user->seller?->isVerified();
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) {
            return true; // Admin override
        }
        return $user->hasRole('seller')
            && $user->seller
            && $product->store_id === $user->seller->store?->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    public function manageStock(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
