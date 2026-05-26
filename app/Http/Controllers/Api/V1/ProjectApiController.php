<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $projects = $user->ownedProjects()
            ->merge($user->projects)
            ->unique('id')
            ->values();

        return response()->json(['data' => $projects]);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request->user(), $project);
        $project->load(['tasks', 'goals', 'members:id,name,email']);

        return response()->json(['data' => $project]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|string|in:active,paused,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['owner_id'] = $request->user()->id;
        $project = Project::create($validated);

        return response()->json(['data' => $project], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request->user(), $project);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|string|in:active,paused,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($validated);

        return response()->json(['data' => $project]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request->user(), $project);
        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }

    private function authorizeProject($user, Project $project): void
    {
        if ($project->owner_id !== $user->id && !$project->members()->where('user_id', $user->id)->exists()) {
            abort(403, 'Not authorized to access this project.');
        }
    }
}
