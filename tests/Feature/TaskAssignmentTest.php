<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Project $project;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->project = Project::factory()->create(['owner_id' => $this->owner->id]);
        $this->project->members()->attach($this->owner->id, ['role' => 'owner']);

        $this->member = User::factory()->create();
        $this->project->members()->attach($this->member->id, ['role' => 'member']);
    }

    public function test_owner_can_create_task_assigned_to_another_member(): void
    {
        $response = $this->actingAs($this->owner)->post("/projects/{$this->project->id}/tasks", [
            'title' => 'Team Task',
            'priority' => 'medium',
            'status' => 'todo',
            'assigned_to' => $this->member->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Team Task',
            'project_id' => $this->project->id,
            'assigned_to' => $this->member->id,
        ]);
    }

    public function test_member_cannot_create_task_assigned_to_another_member(): void
    {
        $other = User::factory()->create();
        $this->project->members()->attach($other->id, ['role' => 'member']);

        $response = $this->actingAs($this->member)->post("/projects/{$this->project->id}/tasks", [
            'title' => 'Hijack Task',
            'priority' => 'medium',
            'status' => 'todo',
            'assigned_to' => $other->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('tasks', ['title' => 'Hijack Task']);
    }

    public function test_member_can_take_open_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => null,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->member)->post("/tasks/{$task->id}/assign-to-me");

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $this->member->id,
        ]);
    }

    public function test_member_cannot_take_task_assigned_to_someone_else(): void
    {
        $other = User::factory()->create();
        $this->project->members()->attach($other->id, ['role' => 'member']);

        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => $other->id,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->member)->post("/tasks/{$task->id}/assign-to-me");

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $other->id,
        ]);
    }

    public function test_admin_can_assign_open_task_to_member(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => null,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)->post("/tasks/{$task->id}/assign", [
            'assigned_to' => $this->member->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $this->member->id,
        ]);
    }

    public function test_member_cannot_assign_task_to_other_member(): void
    {
        $other = User::factory()->create();
        $this->project->members()->attach($other->id, ['role' => 'member']);

        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => null,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->member)->post("/tasks/{$task->id}/assign", [
            'assigned_to' => $other->id,
        ]);

        $response->assertForbidden();
    }

    public function test_task_is_assigned_to_creator_by_default(): void
    {
        $response = $this->actingAs($this->owner)->post("/projects/{$this->project->id}/tasks", [
            'title' => 'Default Assignment',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Default Assignment',
            'assigned_to' => $this->owner->id,
        ]);
    }

    public function test_assignee_must_be_a_project_member(): void
    {
        $outsider = User::factory()->create();

        $response = $this->actingAs($this->owner)->post("/projects/{$this->project->id}/tasks", [
            'title' => 'Invalid Assignee',
            'priority' => 'medium',
            'status' => 'todo',
            'assigned_to' => $outsider->id,
        ]);

        $response->assertSessionHasErrors('assigned_to');
        $this->assertDatabaseMissing('tasks', ['title' => 'Invalid Assignee']);
    }
}
