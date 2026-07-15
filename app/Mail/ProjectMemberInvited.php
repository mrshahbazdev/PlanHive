<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectMemberInvited extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Project $project,
        public User $inviter,
        public ProjectInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->project->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.project-member-invited',
            with: [
                'projectName' => $this->project->name,
                'inviterName' => $this->inviter->name,
                'url' => url("/invitations/{$this->invitation->token}"),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
