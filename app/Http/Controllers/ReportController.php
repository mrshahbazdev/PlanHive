<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $projectIds = $user->ownedProjects()->pluck('id')
            ->merge($user->projects()->pluck('projects.id'))
            ->unique();

        $projects = Project::whereIn('id', $projectIds)->withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'done'),
            'tasks as active_tasks_count' => fn ($q) => $q->whereNotIn('status', ['done', 'cancelled']),
            'tasks as overdue_tasks_count' => fn ($q) => $q->where('due_date', '<', now())->whereNotIn('status', ['done', 'cancelled']),
            'goals',
            'goals as completed_goals_count' => fn ($q) => $q->where('status', 'completed'),
        ])->get();

        $tasksByMonth = Task::whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->where('updated_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $tasksByPriority = Task::whereIn('project_id', $projectIds)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $tasksByStatus = Task::whereIn('project_id', $projectIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $upcomingDeadlines = Task::whereIn('project_id', $projectIds)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(14))
            ->with('project:id,name,color')
            ->orderBy('due_date')
            ->limit(15)
            ->get(['id', 'title', 'due_date', 'priority', 'status', 'project_id']);

        $productivityScore = $projects->count() > 0
            ? round($projects->sum('completed_tasks_count') / max($projects->sum('tasks_count'), 1) * 100)
            : 0;

        return Inertia::render('Reports/Index', [
            'projects' => $projects,
            'tasksByMonth' => $tasksByMonth,
            'tasksByPriority' => $tasksByPriority,
            'tasksByStatus' => $tasksByStatus,
            'upcomingDeadlines' => $upcomingDeadlines,
            'productivityScore' => $productivityScore,
            'summary' => [
                'total_projects' => $projects->count(),
                'total_tasks' => $projects->sum('tasks_count'),
                'completed_tasks' => $projects->sum('completed_tasks_count'),
                'overdue_tasks' => $projects->sum('overdue_tasks_count'),
                'total_goals' => $projects->sum('goals_count'),
                'completed_goals' => $projects->sum('completed_goals_count'),
            ],
        ]);
    }
}
