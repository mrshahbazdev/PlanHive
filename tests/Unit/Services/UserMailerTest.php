<?php

namespace Tests\Unit\Services;

use App\Mail\TestEmail;
use App\Models\User;
use App\Services\UserMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserMailerTest extends TestCase
{
    use RefreshDatabase;

    public function test_falls_back_to_default_mailer_when_user_has_no_smtp_config(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        UserMailer::send($user, 'recipient@example.com', new TestEmail($user->name));

        Mail::assertSent(TestEmail::class, function ($mail) {
            return $mail->hasTo('recipient@example.com');
        });
    }

    public function test_builds_custom_mailer_when_user_has_smtp_config(): void
    {
        $user = User::factory()->create([
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'secret',
            'smtp_encryption' => 'tls',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'Example User',
        ]);

        $mailer = UserMailer::buildMailer($user);

        $this->assertInstanceOf(Mailer::class, $mailer);
    }
}
