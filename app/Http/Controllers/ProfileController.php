<?php

namespace App\Http\Controllers;

use App\Exceptions\MailDeliveryException;
use App\Mail\TestEmail;
use App\Services\UserMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'locale' => ['required', 'in:en,de'],
            'timezone' => ['required', 'string', 'max:100'],
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    public function updateSmtp(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'string', 'max:10'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,null'],
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['smtp_encryption'] === 'null') {
            $validated['smtp_encryption'] = null;
        }

        if (! $request->filled('smtp_password')) {
            unset($validated['smtp_password']);
        }

        $request->user()->update($validated);

        return back()->with('success', 'SMTP settings updated successfully');
    }

    public function sendTestEmail(Request $request)
    {
        try {
            UserMailer::send($request->user(), $request->user()->email, new TestEmail($request->user()->name));
        } catch (MailDeliveryException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Test email sent. Check your inbox.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');
        $request->user()->update(['avatar' => $path]);

        return back()->with('success', 'Avatar updated successfully');
    }
}
