<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()
            ->with('project:id,name,color')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('priority'), fn ($q, $p) => $q->where('priority', $p))
            ->when($request->input('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->orderBy('due_date')
            ->paginate($request->input('per_page', 20));

        return response()->json($tasks);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        if ($task->assigned_to !== $request->user()->id && $task->created_by !== $request->user()->id) {
            abort(403);
        }

        $task->load(['project:id,name,color', 'subtasks', 'assignee:id,name']);

        return response()->json(['data' => $task]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:todo,in_progress,review,done,cancelled',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = $request->user()->id;
        if (!isset($validated['assigned_to'])) {
            $validated['assigned_to'] = $request->user()->id;
        }

        $task = Task::create($validated);

        return response()->json(['data' => $task], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:todo,in_progress,review,done,cancelled',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $task->update($validated);

        return response()->json(['data' => $task]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
