<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $username) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PlanHive SMTP test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.test',
            with: [
                'username' => $this->username,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
