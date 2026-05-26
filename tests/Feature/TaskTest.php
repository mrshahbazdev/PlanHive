<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    }

    public function test_tasks_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/tasks');
        $response->assertStatus(200);
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user)->post("/projects/{$this->project->id}/tasks", [
            'title' => 'Test Task',
            'description' => 'A test task',
            'priority' => 'medium',
            'status' => 'todo',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
        ]);
    }

    public function test_user_can_update_task_status(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->patch("/tasks/{$task->id}/status", [
            'status' => 'done',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_user_can_delete_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/tasks/{$task->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
