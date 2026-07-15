<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectMemberAdded extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Project $project,
        public User $inviter,
        public string $role,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been added to {$this->project->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.project-member-added',
            with: [
                'projectName' => $this->project->name,
                'inviterName' => $this->inviter->name,
                'role' => $this->role,
                'url' => url("/projects/{$this->project->id}"),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
