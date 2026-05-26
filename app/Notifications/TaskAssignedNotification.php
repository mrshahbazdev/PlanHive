<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New task assigned: {$this->task->title}")
            ->line("You have been assigned a new task: {$this->task->title}")
            ->line("Project: {$this->task->project?->name}")
            ->line("Priority: {$this->task->priority}")
            ->action('View Task', url("/projects/{$this->task->project_id}"))
            ->line('Thank you for using PlanHive!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'project' => $this->task->project?->name,
            'message' => "You were assigned: {$this->task->title}",
        ];
    }
}
