<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Reminder $reminder)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (in_array($this->reminder->channel, ['email', 'push'])) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: {$this->reminder->title}")
            ->line("This is your reminder: {$this->reminder->title}")
            ->line("Scheduled for: {$this->reminder->remind_at->format('M d, Y H:i')}")
            ->action('View Reminders', url('/reminders'))
            ->line('Thank you for using PlanHive!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reminder_due',
            'reminder_id' => $this->reminder->id,
            'title' => $this->reminder->title,
            'message' => "Reminder: {$this->reminder->title}",
        ];
    }
}
