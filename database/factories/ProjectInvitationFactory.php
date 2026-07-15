<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectInvitation>
 */
class ProjectInvitationFactory extends Factory
{
    protected $model = ProjectInvitation::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'invited_by' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement(['boss', 'manager', 'member', 'viewer']),
            'token' => Str::random(64),
            'accepted_at' => null,
            'expires_at' => now()->addDays(7),
        ];
    }
}
