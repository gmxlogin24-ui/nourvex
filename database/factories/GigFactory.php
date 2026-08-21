<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GigFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(['Design', 'Writing', 'Tech', 'Tutoring', 'Other']),
            'description' => fake()->paragraph(),
            'amount' => fake()->numberBetween(500, 20000),
            'status' => 'open',
            'user_id' => User::factory(),
        ];
    }
}
