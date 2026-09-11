<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'business_type' => fake()->randomElement(['individual', 'company']),
            'verification_status' => 'verified',
            'verified_at' => now(),
            'commission_rate' => 5.00,
        ];
    }
}
