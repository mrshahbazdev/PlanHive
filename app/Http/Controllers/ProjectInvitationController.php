<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProjectInvitationController extends Controller
{
    public function show(string $token): Response
    {
        $invitation = ProjectInvitation::with(['project', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            abort(HttpResponse::HTTP_GONE, 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            abort(HttpResponse::HTTP_GONE, 'This invitation has expired.');
        }

        return Inertia::render('Invitations/Show', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = ProjectInvitation::with('project')
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect('/dashboard')->with('error', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect('/dashboard')->with('error', 'This invitation has expired.');
        }

        $user = $request->user();

        if ($user->email !== $invitation->email) {
            return back()->withErrors(['email' => 'This invitation was sent to a different email address.']);
        }

        if ($invitation->project->members()->where('user_id', $user->id)->exists()) {
            $invitation->delete();

            return redirect()->route('projects.show', $invitation->project)
                ->with('success', 'You are already a member of this project.');
        }

        $invitation->project->members()->attach($user->id, [
            'role' => $invitation->role,
            'joined_at' => now(),
        ]);

        $invitation->delete();

        return redirect()->route('projects.show', $invitation->project)
            ->with('success', 'You have joined the project.');
    }
}
