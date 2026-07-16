<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar' => $request->user()->avatar,
                    'locale' => $request->user()->locale,
                    'timezone' => $request->user()->timezone,
                    'is_admin' => $request->user()->is_admin,
                    'smtp_host' => $request->user()->smtp_host,
                    'smtp_port' => $request->user()->smtp_port,
                    'smtp_username' => $request->user()->smtp_username,
                    'smtp_encryption' => $request->user()->smtp_encryption,
                    'smtp_from_address' => $request->user()->smtp_from_address,
                    'smtp_from_name' => $request->user()->smtp_from_name,
                ] : null,
            ],
            'unreadNotificationsCount' => fn () => $request->user()
                ? $request->user()->unreadNotifications()->count()
                : 0,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => app()->getLocale(),
        ]);
    }
}
