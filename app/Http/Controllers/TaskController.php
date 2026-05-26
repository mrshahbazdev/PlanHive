<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tasks = $user->tasks()
            ->with('project:id,name,color', 'assignee:id,name,avatar')
            ->orderBy('due_date')
            ->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:urgent,high,medium,low'],
            'status' => ['sometimes', 'in:todo,in_progress,review,done,cancelled'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'parent_id' => ['nullable', 'exists:tasks,id'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['project_id'] = $project->id;

        $task = Task::create($validated);

        return back()->with('success', __('tasks.created'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:urgent,high,medium,low'],
            'status' => ['sometimes', 'in:todo,in_progress,review,done,cancelled'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $task->update($validated);

        return back()->with('success', __('tasks.updated'));
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', __('tasks.deleted'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => ['required', 'in:todo,in_progress,review,done,cancelled'],
        ]);

        $task->update(['status' => $request->status]);

        return back()->with('success', __('tasks.status_updated'));
    }
}
