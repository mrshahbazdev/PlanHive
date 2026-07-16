<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSmtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_smtp_settings_are_persisted_and_returned_in_inertia_props(): void
    {
        $user = User::factory()->create([
            'smtp_password' => 'secret',
        ]);

        $this->actingAs($user)->put('/profile/smtp', [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'new-secret',
            'smtp_encryption' => 'tls',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'PlanHive',
        ]);

        $user->refresh();

        $this->assertEquals('smtp.example.com', $user->smtp_host);
        $this->assertEquals('587', $user->smtp_port);
        $this->assertEquals('user@example.com', $user->smtp_username);
        $this->assertEquals('tls', $user->smtp_encryption);
        $this->assertEquals('noreply@example.com', $user->smtp_from_address);
        $this->assertEquals('PlanHive', $user->smtp_from_name);

        $response = $this->actingAs($user)->get('/profile');
        $response->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->has('auth.user')
            ->where('auth.user.smtp_host', 'smtp.example.com')
            ->where('auth.user.smtp_port', '587')
            ->where('auth.user.smtp_username', 'user@example.com')
            ->where('auth.user.smtp_encryption', 'tls')
            ->where('auth.user.smtp_from_address', 'noreply@example.com')
            ->where('auth.user.smtp_from_name', 'PlanHive')
            ->missing('auth.user.smtp_password')
        );
    }
}
