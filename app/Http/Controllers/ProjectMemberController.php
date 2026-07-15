<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectMemberAddedNotification;
use App\Notifications\ProjectMemberInvitedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
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

            $user->notify(new ProjectMemberAddedNotification($project, $request->user(), $validated['role']));

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

        Notification::route('mail', $validated['email'])
            ->notify(new ProjectMemberInvitedNotification($project, $request->user(), $invitation));

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
