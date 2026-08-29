<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function update(User $user, Store $store): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->hasRole('seller')
            && $user->seller
            && $store->seller_id === $user->seller->id;
    }
}
