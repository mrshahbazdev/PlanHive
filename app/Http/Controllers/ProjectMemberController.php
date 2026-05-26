<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:boss,manager,member,viewer'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'User is already a member of this project.']);
        }

        $project->members()->attach($user->id, [
            'role' => $validated['role'],
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Member added successfully');
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
