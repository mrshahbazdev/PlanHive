<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\UserIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphService
{
    private const AUTH_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0';
    private const GRAPH_URL = 'https://graph.microsoft.com/v1.0';
    private const SCOPES = 'openid profile email offline_access Calendars.ReadWrite User.Read';

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id' => config('services.microsoft.client_id'),
            'redirect_uri' => config('services.microsoft.redirect_uri'),
            'response_type' => 'code',
            'scope' => self::SCOPES,
            'response_mode' => 'query',
        ]);

        return self::AUTH_URL . '/authorize?' . $params;
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post(self::AUTH_URL . '/token', [
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'code' => $code,
            'redirect_uri' => config('services.microsoft.redirect_uri'),
            'grant_type' => 'authorization_code',
            'scope' => self::SCOPES,
        ]);

        return $response->json();
    }

    public function refreshToken(UserIntegration $integration): bool
    {
        $response = Http::asForm()->post(self::AUTH_URL . '/token', [
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'refresh_token' => $integration->refresh_token,
            'grant_type' => 'refresh_token',
            'scope' => self::SCOPES,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $integration->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $integration->refresh_token,
                'token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);
            return true;
        }

        return false;
    }

    public function getAccessToken(UserIntegration $integration): ?string
    {
        if ($integration->token_expires_at && $integration->token_expires_at->isPast()) {
            if (!$this->refreshToken($integration)) {
                return null;
            }
            $integration->refresh();
        }

        return $integration->access_token;
    }

    public function fetchCalendarEvents(UserIntegration $integration, string $startDate, string $endDate): array
    {
        $token = $this->getAccessToken($integration);
        if (!$token) {
            return [];
        }

        $response = Http::withToken($token)
            ->get(self::GRAPH_URL . '/me/calendarview', [
                'startDateTime' => $startDate,
                'endDateTime' => $endDate,
                '$top' => 100,
                '$select' => 'id,subject,start,end,isAllDay,location,bodyPreview',
            ]);

        if ($response->successful()) {
            return $response->json('value', []);
        }

        Log::warning('Microsoft Graph calendar fetch failed', ['status' => $response->status()]);
        return [];
    }

    public function createCalendarEvent(UserIntegration $integration, CalendarEvent $event): ?string
    {
        $token = $this->getAccessToken($integration);
        if (!$token) {
            return null;
        }

        $body = [
            'subject' => $event->title,
            'body' => [
                'contentType' => 'text',
                'content' => $event->description ?? '',
            ],
            'start' => [
                'dateTime' => $event->start_at->format('Y-m-d\TH:i:s'),
                'timeZone' => $event->user->timezone ?? 'UTC',
            ],
            'end' => [
                'dateTime' => ($event->end_at ?? $event->start_at->addHour())->format('Y-m-d\TH:i:s'),
                'timeZone' => $event->user->timezone ?? 'UTC',
            ],
            'isAllDay' => $event->all_day ?? false,
        ];

        if ($event->location) {
            $body['location'] = ['displayName' => $event->location];
        }

        $response = Http::withToken($token)
            ->post(self::GRAPH_URL . '/me/events', $body);

        if ($response->successful()) {
            return $response->json('id');
        }

        Log::warning('Microsoft Graph event creation failed', ['status' => $response->status()]);
        return null;
    }

    public function deleteCalendarEvent(UserIntegration $integration, string $outlookEventId): bool
    {
        $token = $this->getAccessToken($integration);
        if (!$token) {
            return false;
        }

        $response = Http::withToken($token)
            ->delete(self::GRAPH_URL . "/me/events/{$outlookEventId}");

        return $response->successful();
    }

    public function getUserProfile(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)->get(self::GRAPH_URL . '/me');

        return $response->successful() ? $response->json() : null;
    }

    public function sendTeamsMessage(UserIntegration $integration, string $chatId, string $message): bool
    {
        $token = $this->getAccessToken($integration);
        if (!$token) {
            return false;
        }

        $response = Http::withToken($token)
            ->post(self::GRAPH_URL . "/chats/{$chatId}/messages", [
                'body' => [
                    'contentType' => 'html',
                    'content' => $message,
                ],
            ]);

        return $response->successful();
    }
}
