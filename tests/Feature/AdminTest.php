<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}");
        $response->assertStatus(200);
    }

    public function test_admin_can_toggle_user_admin_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/toggle-admin");
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_admin);
    }

    public function test_admin_cannot_toggle_own_admin_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle-admin");
        $response->assertRedirect();

        $admin->refresh();
        $this->assertTrue($admin->is_admin);
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/audit-logs');
        $response->assertStatus(200);
    }
}
