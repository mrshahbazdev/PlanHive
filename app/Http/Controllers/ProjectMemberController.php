<?php

namespace App\Http\Controllers;

use App\Exceptions\MailDeliveryException;
use App\Mail\ProjectMemberAdded;
use App\Mail\ProjectMemberInvited;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectMemberAddedNotification;
use App\Services\UserMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:boss,manager,member,viewer'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            if ($project->members()->where('user_id', $user->id)->exists()) {
                return back()->withErrors(['email' => 'User is already a member of this project.']);
            }

            $project->members()->attach($user->id, [
                'role' => $validated['role'],
                'joined_at' => now(),
            ]);

            try {
                UserMailer::send($request->user(), $user->email, new ProjectMemberAdded($project, $request->user(), $validated['role']));
                $user->notify(new ProjectMemberAddedNotification($project, $request->user(), $validated['role']));
            } catch (MailDeliveryException $e) {
                return back()->with('error', "Member added, but the email could not be sent: {$e->getMessage()}");
            }

            return back()->with('success', 'Member added successfully');
        }

        $invitation = ProjectInvitation::updateOrCreate(
            ['project_id' => $project->id, 'email' => $validated['email']],
            [
                'invited_by' => $request->user()->id,
                'role' => $validated['role'],
                'token' => Str::random(64),
                'accepted_at' => null,
                'expires_at' => now()->addDays(7),
            ]
        );

        try {
            UserMailer::send($request->user(), $validated['email'], new ProjectMemberInvited($project, $request->user(), $invitation));
        } catch (MailDeliveryException $e) {
            $invitation->delete();

            return back()->with('error', "Invitation could not be sent: {$e->getMessage()}");
        }

        return back()->with('success', "An invitation has been sent to {$validated['email']}");
    }

    public function update(Request $request, Project $project, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:boss,manager,member,viewer'],
        ]);

        $project->members()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Member role updated');
    }

    public function destroy(Project $project, User $user)
    {
        $project->members()->detach($user->id);

        return back()->with('success', 'Member removed');
    }
}
