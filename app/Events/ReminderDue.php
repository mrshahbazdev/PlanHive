<?php

namespace App\Events;

use App\Models\Reminder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderDue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Reminder $reminder)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->reminder->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->reminder->id,
            'title' => $this->reminder->title,
            'remind_at' => $this->reminder->remind_at->toIso8601String(),
            'channel' => $this->reminder->channel,
        ];
    }
}
