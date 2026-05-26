<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $projects = $user->projects()->withCount(['tasks', 'members', 'goals'])->get();
        $ownedProjects = $user->ownedProjects()->withCount(['tasks', 'members', 'goals'])->get();
        $allProjects = $projects->merge($ownedProjects)->unique('id');

        return Inertia::render('Projects/Index', [
            'projects' => $allProjects,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:7'],
            'status' => ['sometimes', 'in:active,on_hold,completed,archived'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project = $request->user()->ownedProjects()->create($validated);
        $project->members()->attach($request->user()->id, ['role' => 'owner']);

        return redirect()->route('projects.show', $project)
            ->with('success', __('projects.created'));
    }

    public function show(Project $project): Response
    {
        $project->load([
            'members',
            'tasks' => fn ($q) => $q->whereNull('parent_id')->orderBy('sort_order'),
            'tasks.assignee:id,name,avatar',
            'tasks.subtasks',
            'goals',
            'notes' => fn ($q) => $q->orderByDesc('is_pinned')->latest(),
        ]);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:7'],
            'status' => ['sometimes', 'in:active,on_hold,completed,archived'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', __('projects.updated'));
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', __('projects.deleted'));
    }
}
