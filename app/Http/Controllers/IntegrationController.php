<?php

namespace App\Http\Controllers;

use App\Models\UserIntegration;
use App\Services\MicrosoftGraphService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationController extends Controller
{
    public function __construct(private MicrosoftGraphService $graph)
    {
    }

    public function index(Request $request): Response
    {
        $integrations = $request->user()->integrations()
            ->get()
            ->keyBy('provider')
            ->map(fn ($i) => [
                'id' => $i->id,
                'provider' => $i->provider,
                'connected_at' => $i->connected_at,
                'settings' => $i->settings,
            ]);

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrations,
            'microsoftConfigured' => $request->user()->integrations()->where('provider', 'microsoft_app')->exists(),
        ]);
    }

    public function saveMicrosoftAppCredentials(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
        ]);

        $request->user()->integrations()->updateOrCreate(
            ['provider' => 'microsoft_app'],
            [
                'access_token' => 'credentials',
                'settings' => [
                    'client_id' => $request->client_id,
                    'client_secret' => $request->client_secret,
                ],
            ]
        );

        return back()->with('success', 'Microsoft App credentials saved successfully.');
    }

    public function connectOutlook(Request $request): \Illuminate\Http\RedirectResponse
    {
        $url = $this->graph->getAuthUrl($request->user());
        if (!$url) {
            return back()->with('error', 'Microsoft App credentials not configured.');
        }
        return redirect()->away($url);
    }

    public function outlookCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('integrations.index')
                ->with('error', 'Microsoft authorization was denied.');
        }

        $tokenData = $this->graph->exchangeCode($request->user(), $request->code);

        if (!isset($tokenData['access_token'])) {
            return redirect()->route('integrations.index')
                ->with('error', 'Failed to obtain Microsoft access token.');
        }

        $profile = $this->graph->getUserProfile($tokenData['access_token']);

        $request->user()->integrations()->updateOrCreate(
            ['provider' => 'outlook'],
            [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                'settings' => [
                    'email' => $profile['mail'] ?? $profile['userPrincipalName'] ?? null,
                    'name' => $profile['displayName'] ?? null,
                ],
            ]
        );

        return redirect()->route('integrations.index')
            ->with('success', 'Microsoft Outlook connected successfully!');
    }

    public function connectTeams(Request $request)
    {
        $request->validate([
            'webhook_url' => ['required', 'url', 'starts_with:https://'],
        ]);

        $request->user()->integrations()->updateOrCreate(
            ['provider' => 'teams'],
            [
                'access_token' => 'webhook',
                'settings' => [
                    'webhook_url' => $request->webhook_url,
                ],
            ]
        );

        return back()->with('success', 'Microsoft Teams webhook connected!');
    }

    public function syncCalendar(Request $request)
    {
        $integration = $request->user()->integrations()
            ->where('provider', 'outlook')
            ->first();

        if (!$integration) {
            return back()->with('error', 'Outlook not connected.');
        }

        $start = now()->startOfMonth()->toIso8601String();
        $end = now()->addMonths(3)->endOfMonth()->toIso8601String();

        $outlookEvents = $this->graph->fetchCalendarEvents($integration, $start, $end);

        $imported = 0;
        foreach ($outlookEvents as $event) {
            $existing = $request->user()->calendarEvents()
                ->where('outlook_event_id', $event['id'])
                ->first();

            if (!$existing) {
                $request->user()->calendarEvents()->create([
                    'title' => $event['subject'] ?? 'Untitled',
                    'start_at' => $event['start']['dateTime'] ?? now(),
                    'end_at' => $event['end']['dateTime'] ?? null,
                    'all_day' => $event['isAllDay'] ?? false,
                    'location' => $event['location']['displayName'] ?? null,
                    'description' => $event['bodyPreview'] ?? null,
                    'outlook_event_id' => $event['id'],
                ]);
                $imported++;
            }
        }

        return back()->with('success', "Synced {$imported} events from Outlook.");
    }

    public function disconnect(Request $request, string $provider)
    {
        $request->user()->integrations()->where('provider', $provider)->delete();

        return back()->with('success', ucfirst($provider) . ' disconnected.');
    }

    public function testTeamsWebhook(Request $request)
    {
        $integration = $request->user()->integrations()
            ->where('provider', 'teams')
            ->first();

        if (!$integration || !($integration->settings['webhook_url'] ?? null)) {
            return back()->with('error', 'Teams webhook not configured.');
        }

        $response = \Illuminate\Support\Facades\Http::post($integration->settings['webhook_url'], [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => json_encode([
                    'type' => 'AdaptiveCard',
                    'version' => '1.4',
                    'body' => [[
                        'type' => 'TextBlock',
                        'text' => '🐝 PlanHive is connected! You will receive task and reminder notifications here.',
                        'wrap' => true,
                    ]],
                ]),
            ]],
        ]);

        return $response->successful()
            ? back()->with('success', 'Test message sent to Teams!')
            : back()->with('error', 'Failed to send test message.');
    }
}
