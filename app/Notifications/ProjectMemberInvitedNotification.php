<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectMemberInvitedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Project $project,
        private User $inviter,
        private ProjectInvitation $invitation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You're invited to join {$this->project->name}")
            ->greeting('Hello,')
            ->line("{$this->inviter->name} invited you to collaborate on the project '{$this->project->name}'.")
            ->action('Accept Invitation', url("/invitations/{$this->invitation->token}"))
            ->line('This invitation will expire soon, so please accept it as soon as possible.')
            ->line('Thank you for using PlanHive!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_member_invited',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'message' => "{$this->inviter->name} invited you to {$this->project->name}.",
        ];
    }
}
