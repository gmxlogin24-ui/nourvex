<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->create();
        return [
            'title' => fake()->sentence(3),
            'seller_name' => $user->name,
            'description' => fake()->paragraph(),
            'amount' => fake()->numberBetween(500, 50000),
            'category' => fake()->randomElement(['Fashion', 'Electronics', 'Books', 'Food', 'Services', 'Rentals', 'Other']),
            'location' => fake()->randomElement(['Hostel A', 'Hostel B', 'Main Gate', 'Campus-wide']),
            'condition' => fake()->randomElement(['New', 'Like New', 'Used']),
            'listing_type' => 'item',
            'status' => 'available',
            'user_id' => $user->id,
        ];
    }
}
