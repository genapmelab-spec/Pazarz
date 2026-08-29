<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function store(User $user): bool
    {
        return $user->hasRole('customer');
    }

    public function reply(User $user, Review $review): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        // Seller can reply to reviews on their products
        if ($user->hasRole('seller') && $user->seller) {
            return $review->product->store_id === $user->seller->store?->id;
        }
        return false;
    }

    public function moderate(User $user, Review $review): bool
    {
        return $user->hasRole('admin');
    }
}
