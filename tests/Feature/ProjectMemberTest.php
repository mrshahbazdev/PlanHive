<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectMemberAddedNotification;
use App\Notifications\ProjectMemberInvitedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
    }

    public function test_existing_user_receives_notification_when_added_to_project(): void
    {
        Notification::fake();

        $project = Project::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $project->members()->attach($this->owner->id, ['role' => 'owner']);

        $member = User::factory()->create();

        $response = $this->actingAs($this->owner)->post("/projects/{$project->id}/members", [
            'email' => $member->email,
            'role' => 'manager',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'manager',
        ]);

        Notification::assertSentTo($member, ProjectMemberAddedNotification::class);
    }

    public function test_invitation_is_created_for_new_email_and_notification_is_sent(): void
    {
        Notification::fake();

        $project = Project::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $project->members()->attach($this->owner->id, ['role' => 'owner']);

        $email = 'new-member@example.com';

        $response = $this->actingAs($this->owner)->post("/projects/{$project->id}/members", [
            'email' => $email,
            'role' => 'viewer',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('project_invitations', [
            'project_id' => $project->id,
            'email' => $email,
            'role' => 'viewer',
        ]);

        $invitation = ProjectInvitation::where('project_id', $project->id)
            ->where('email', $email)
            ->first();

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            ProjectMemberInvitedNotification::class,
            function ($notification, $channels, $notifiable) use ($invitation) {
                return $notifiable->routes['mail'] === $invitation->email;
            }
        );
    }

    public function test_invitation_can_be_accepted_by_existing_user(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $project->members()->attach($this->owner->id, ['role' => 'owner']);

        $member = User::factory()->create();

        $invitation = ProjectInvitation::factory()->create([
            'project_id' => $project->id,
            'email' => $member->email,
            'role' => 'member',
        ]);

        $response = $this->actingAs($member)->post("/invitations/{$invitation->token}/accept");

        $response->assertRedirect("/projects/{$project->id}");

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $this->assertDatabaseMissing('project_invitations', ['id' => $invitation->id]);
    }

    public function test_invitation_can_be_consumed_during_registration(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $project->members()->attach($this->owner->id, ['role' => 'owner']);

        $email = 'invite-register@example.com';

        $invitation = ProjectInvitation::factory()->create([
            'project_id' => $project->id,
            'email' => $email,
            'role' => 'boss',
        ]);

        $response = $this->post('/register', [
            'name' => 'Invited User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'locale' => 'en',
            'invitation_token' => $invitation->token,
        ]);

        $response->assertRedirect("/projects/{$project->id}");

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'role' => 'boss',
        ]);

        $this->assertDatabaseMissing('project_invitations', ['id' => $invitation->id]);
    }
}
