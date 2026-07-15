<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(Request $request): Response
    {
        $invitation = null;

        if ($token = $request->input('invitation_token')) {
            $invitation = ProjectInvitation::with('project')
                ->where('token', $token)
                ->whereNull('accepted_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        return Inertia::render('Auth/Register', [
            'invitation' => $invitation,
        ]);
    }

    public function store(Request $request)
    {
        $invitation = null;

        if ($token = $request->input('invitation_token')) {
            $invitation = ProjectInvitation::with('project')
                ->where('token', $token)
                ->whereNull('accepted_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'locale' => ['sometimes', 'in:en,de'],
        ];

        if ($invitation) {
            $rules['email'] = [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                Rule::in([$invitation->email]),
            ];
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'locale' => $validated['locale'] ?? 'en',
        ]);

        Auth::login($user);

        if ($invitation) {
            $invitation->project->members()->attach($user->id, [
                'role' => $invitation->role,
                'joined_at' => now(),
            ]);

            $invitation->delete();

            return redirect()->route('projects.show', $invitation->project)
                ->with('success', 'You have joined the project.');
        }

        return redirect('/dashboard');
    }
}
