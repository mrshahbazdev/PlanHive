<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_projects_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/projects');
        $response->assertStatus(200);
    }

    public function test_user_can_create_project(): void
    {
        $response = $this->actingAs($this->user)->post('/projects', [
            'name' => 'Test Project',
            'description' => 'A test project',
            'color' => '#14b8a6',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'owner_id' => $this->user->id,
        ]);
    }

    public function test_user_can_view_own_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get("/projects/{$project->id}");
        $response->assertStatus(200);
    }

    public function test_user_can_update_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put("/projects/{$project->id}", [
            'name' => 'Updated Project',
            'description' => 'Updated description',
            'color' => '#f59e0b',
            'status' => 'paused',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Project']);
    }

    public function test_user_can_delete_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete("/projects/{$project->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
