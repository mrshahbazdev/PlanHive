<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $projects = $user->projects()->withCount('tasks')->get();
        $ownedProjects = $user->ownedProjects()->withCount('tasks')->get();
        $allProjects = $projects->merge($ownedProjects)->unique('id');

        $projectIds = $allProjects->pluck('id');

        $upcomingTasks = Task::where(function ($q) use ($user, $projectIds) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id)
                  ->orWhereIn('project_id', $projectIds);
            })
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(10)
            ->with('project:id,name,color')
            ->get();

        $calendarEvents = $user->calendarEvents()
            ->with('project:id,name,color')
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_at->toIso8601String(),
                'end' => $event->end_at->toIso8601String(),
                'allDay' => $event->all_day,
                'color' => $event->color_override ?? $event->project?->color ?? '#14b8a6',
                'extendedProps' => [
                    'project_id' => $event->project_id,
                    'project_name' => $event->project?->name,
                    'description' => $event->description,
                    'location' => $event->location,
                ],
            ]);

        $allTasksForCalendar = Task::where(function ($q) use ($user, $projectIds) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id)
                  ->orWhereIn('project_id', $projectIds);
            })
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('due_date')
            ->with('project:id,name,color')
            ->get();

        $taskEvents = $allTasksForCalendar->map(fn ($task) => [
            'id' => 'task-' . $task->id,
            'title' => $task->title,
            'start' => $task->due_date->toIso8601String(),
            'allDay' => true,
            'color' => $task->project?->color ?? '#f59e0b',
            'extendedProps' => [
                'type' => 'task',
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
                'priority' => $task->priority,
                'status' => $task->status,
            ],
        ]);

        $goals = Goal::whereIn('project_id', $projectIds)
            ->whereNotNull('target_date')
            ->whereNotIn('status', ['achieved', 'missed'])
            ->with('project:id,name,color')
            ->get();

        $goalEvents = $goals->map(fn ($goal) => [
            'id' => 'goal-' . $goal->id,
            'title' => $goal->title,
            'start' => $goal->target_date->toIso8601String(),
            'allDay' => true,
            'color' => $goal->project?->color ?? '#8b5cf6',
            'extendedProps' => [
                'type' => 'goal',
                'project_id' => $goal->project_id,
                'project_name' => $goal->project?->name,
                'progress' => $goal->progress,
                'status' => $goal->status,
            ],
        ]);

        $goalsProgress = $goals->map(fn ($goal) => [
            'id' => $goal->id,
            'title' => $goal->title,
            'progress' => $goal->progress,
            'status' => $goal->status,
            'project_name' => $goal->project?->name,
        ]);

        return Inertia::render('Dashboard', [
            'projects' => $allProjects,
            'upcomingTasks' => $upcomingTasks,
            'calendarEvents' => $calendarEvents->merge($taskEvents)->merge($goalEvents)->values(),
            'goalsProgress' => $goalsProgress,
            'stats' => [
                'total_projects' => $allProjects->count(),
                'active_tasks' => Task::where(function ($q) use ($user, $projectIds) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id)
                      ->orWhereIn('project_id', $projectIds);
                })->whereNotIn('status', ['done', 'cancelled'])->count(),
                'due_today' => Task::where(function ($q) use ($user, $projectIds) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id)
                      ->orWhereIn('project_id', $projectIds);
                })->whereDate('due_date', today())->count(),
                'overdue' => Task::where(function ($q) use ($user, $projectIds) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id)
                      ->orWhereIn('project_id', $projectIds);
                })->where('due_date', '<', now())->whereNotIn('status', ['done', 'cancelled'])->count(),
            ],
        ]);
    }
}
