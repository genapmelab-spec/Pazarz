<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\User;

class SubOrderPolicy
{
    public function view(User $user, SubOrder $subOrder): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('seller') && $user->seller) {
            return $subOrder->store_id === $user->seller->store?->id;
        }
        return false;
    }

    public function confirm(User $user, SubOrder $subOrder): bool
    {
        return $this->view($user, $subOrder);
    }

    public function ship(User $user, SubOrder $subOrder): bool
    {
        return $this->view($user, $subOrder);
    }

    public function cancel(User $user, SubOrder $subOrder): bool
    {
        return $this->view($user, $subOrder);
    }
}
