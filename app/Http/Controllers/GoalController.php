<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $projectIds = $user->projects()->pluck('projects.id')
            ->merge($user->ownedProjects()->pluck('id'));

        $goals = Goal::whereIn('project_id', $projectIds)
            ->with('project:id,name,color')
            ->latest()
            ->paginate(20);

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:not_started,in_progress,achieved,missed'],
        ]);

        $validated['project_id'] = $project->id;
        $validated['created_by'] = $request->user()->id;

        Goal::create($validated);

        return back()->with('success', __('goals.created'));
    }

    public function update(Request $request, Goal $goal)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status' => ['sometimes', 'in:not_started,in_progress,achieved,missed'],
        ]);

        $goal->update($validated);

        return back()->with('success', __('goals.updated'));
    }

    public function destroy(Goal $goal)
    {
        $goal->delete();
        return back()->with('success', __('goals.deleted'));
    }
}
