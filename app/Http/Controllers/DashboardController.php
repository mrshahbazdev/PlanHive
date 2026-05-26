<?php

namespace App\Http\Controllers;

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

        $upcomingTasks = $user->tasks()
            ->where('status', '!=', 'done')
            ->where('status', '!=', 'cancelled')
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

        $taskEvents = $upcomingTasks->map(fn ($task) => [
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

        return Inertia::render('Dashboard', [
            'projects' => $allProjects,
            'upcomingTasks' => $upcomingTasks,
            'calendarEvents' => $calendarEvents->merge($taskEvents)->values(),
            'stats' => [
                'total_projects' => $allProjects->count(),
                'active_tasks' => $user->tasks()->whereNotIn('status', ['done', 'cancelled'])->count(),
                'due_today' => $user->tasks()->whereDate('due_date', today())->count(),
                'overdue' => $user->tasks()->where('due_date', '<', now())->whereNotIn('status', ['done', 'cancelled'])->count(),
            ],
        ]);
    }
}
