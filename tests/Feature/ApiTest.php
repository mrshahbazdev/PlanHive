<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test', ['*'])->plainTextToken;
    }

    public function test_api_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(401);
    }

    public function test_api_list_projects(): void
    {
        Project::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->withToken($this->token)->getJson('/api/v1/projects');
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_api_create_project(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/projects', [
            'name' => 'API Project',
            'description' => 'Created via API',
            'color' => '#14b8a6',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'API Project']);
    }

    public function test_api_list_tasks(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);
        Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/tasks');
        $response->assertStatus(200);
    }

    public function test_api_create_task(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->withToken($this->token)->postJson('/api/v1/tasks', [
            'project_id' => $project->id,
            'title' => 'API Task',
            'priority' => 'high',
            'status' => 'todo',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'API Task']);
    }

    public function test_api_token_management_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/api-tokens');
        $response->assertStatus(200);
    }

    public function test_user_can_create_api_token(): void
    {
        $response = $this->actingAs($this->user)->post('/api-tokens', [
            'name' => 'My Token',
            'abilities' => ['projects:read', 'tasks:read'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'My Token']);
    }
}
