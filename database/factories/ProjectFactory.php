<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'status' => fake()->randomElement(['active', 'paused', 'completed', 'archived']),
            'owner_id' => User::factory(),
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
        ];
    }
}
