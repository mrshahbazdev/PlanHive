<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $projectIds = $user->projects()->pluck('projects.id')
            ->merge($user->ownedProjects()->pluck('id'));

        $tasks = Task::where(function ($q) use ($user, $projectIds) {
            $q->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id)
                ->orWhereIn('project_id', $projectIds);
        })
            ->with('project:id,name,color,owner_id', 'project.members:id,name,avatar', 'assignee:id,name,avatar')
            ->orderBy('due_date')
            ->get();

        $projects = $user->projects()
            ->with('members:id,name,avatar')
            ->get(['projects.id', 'projects.name', 'projects.color', 'projects.owner_id'])
            ->merge($user->ownedProjects()->with('members:id,name,avatar')->get(['id', 'name', 'color', 'owner_id']))
            ->unique('id');

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorizeMember($request->user(), $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:urgent,high,medium,low'],
            'status' => ['sometimes', 'in:todo,in_progress,review,done,cancelled'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => [
                'nullable',
                Rule::in($project->memberIds()),
            ],
            'parent_id' => ['nullable', 'exists:tasks,id'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['project_id'] = $project->id;
        $validated['assigned_to'] = $this->resolveAssignee($request, $project, $validated['assigned_to'] ?? null);

        $task = Task::create($validated);

        return back()->with('success', __('tasks.created'));
    }

    public function update(Request $request, Task $task)
    {
        $project = $task->project;
        $this->authorizeMember($request->user(), $project);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:urgent,high,medium,low'],
            'status' => ['sometimes', 'in:todo,in_progress,review,done,cancelled'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => [
                'nullable',
                Rule::in($project->memberIds()),
            ],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        if (array_key_exists('assigned_to', $validated)) {
            $validated['assigned_to'] = $this->resolveAssignee($request, $project, $validated['assigned_to'] ?? null, $task);
        }

        $task->update($validated);

        return back()->with('success', __('tasks.updated'));
    }

    public function assign(Request $request, Task $task)
    {
        $project = $task->project;
        $user = $request->user();

        $this->authorizeMember($user, $project);

        if (! $project->isAdmin($user)) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'Only project admins can assign tasks to others.');
        }

        $validated = $request->validate([
            'assigned_to' => ['required', Rule::in($project->memberIds())],
        ]);

        $task->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', __('tasks.assigned'));
    }

    public function assignToMe(Request $request, Task $task)
    {
        $project = $task->project;
        $user = $request->user();

        $this->authorizeMember($user, $project);

        if ($task->assigned_to !== null && $task->assigned_to !== $user->id && ! $project->isAdmin($user)) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'This task is already assigned to someone else.');
        }

        $task->update(['assigned_to' => $user->id]);

        return back()->with('success', __('tasks.assigned_to_me'));
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

    private function authorizeMember($user, Project $project): void
    {
        if (! $project->isMember($user)) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'You are not a member of this project.');
        }
    }

    private function resolveAssignee(Request $request, Project $project, ?int $assigneeId, ?Task $existingTask = null): ?int
    {
        if ($assigneeId === null) {
            if ($existingTask !== null && $project->isAdmin($request->user())) {
                return null;
            }

            return $existingTask?->assigned_to ?? $request->user()->id;
        }

        if ($assigneeId === $request->user()->id) {
            return $assigneeId;
        }

        if (! $project->isAdmin($request->user())) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'You can only assign tasks to yourself.');
        }

        return $assigneeId;
    }
}
