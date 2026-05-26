<?php

namespace App\Console\Commands;

use App\Events\ReminderDue;
use App\Models\Reminder;
use App\Notifications\ReminderDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ProcessReminders extends Command
{
    protected $signature = 'reminders:process';
    protected $description = 'Process due reminders and send notifications';

    public function handle(): int
    {
        $reminders = Reminder::where('is_sent', false)
            ->where('remind_at', '<=', now())
            ->with('user.integrations')
            ->get();

        $count = 0;

        foreach ($reminders as $reminder) {
            $user = $reminder->user;

            $user->notify(new ReminderDueNotification($reminder));
            ReminderDue::dispatch($reminder);

            // Teams webhook if connected
            $teamsIntegration = $user->integrations->where('provider', 'teams')->first();
            if ($teamsIntegration && ($teamsIntegration->settings['webhook_url'] ?? null)) {
                Http::post($teamsIntegration->settings['webhook_url'], [
                    'type' => 'message',
                    'attachments' => [[
                        'contentType' => 'application/vnd.microsoft.card.adaptive',
                        'content' => json_encode([
                            'type' => 'AdaptiveCard',
                            'version' => '1.4',
                            'body' => [
                                ['type' => 'TextBlock', 'text' => "⏰ Reminder: {$reminder->title}", 'weight' => 'bolder', 'wrap' => true],
                                ['type' => 'TextBlock', 'text' => "Scheduled: {$reminder->remind_at->format('M d, Y H:i')}", 'isSubtle' => true],
                            ],
                        ]),
                    ]],
                ]);
            }

            $reminder->update(['is_sent' => true, 'sent_at' => now()]);
            $count++;
        }

        $this->info("Processed {$count} reminders.");
        return Command::SUCCESS;
    }
}
