<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectMemberAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Project $project,
        private User $inviter,
        private string $role,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You've been added to {$this->project->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->inviter->name} added you to the project '{$this->project->name}' as a {$this->role}.")
            ->action('View Project', url("/projects/{$this->project->id}"))
            ->line('Thank you for using PlanHive!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_member_added',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'role' => $this->role,
            'message' => "{$this->inviter->name} added you to {$this->project->name}.",
        ];
    }
}
