<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalTasks = Task::count();
        $totalNotes = Note::count();
        $totalDocuments = Document::count();
        $totalContacts = Contact::count();

        $newUsersThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $activeTasksCount = Task::whereNotIn('status', ['done', 'cancelled'])->count();
        $completedTasksCount = Task::where('status', 'done')->count();
        $overdueTasksCount = Task::where('due_date', '<', now())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        $userGrowth = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $tasksByStatus = Task::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentUsers = User::latest()->limit(10)->get(['id', 'name', 'email', 'is_admin', 'created_at']);
        $recentLogs = AuditLog::with('user:id,name')
            ->latest()
            ->limit(15)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_projects' => $totalProjects,
                'total_tasks' => $totalTasks,
                'total_notes' => $totalNotes,
                'total_documents' => $totalDocuments,
                'total_contacts' => $totalContacts,
                'new_users_month' => $newUsersThisMonth,
                'active_tasks' => $activeTasksCount,
                'completed_tasks' => $completedTasksCount,
                'overdue_tasks' => $overdueTasksCount,
            ],
            'userGrowth' => $userGrowth,
            'tasksByStatus' => $tasksByStatus,
            'recentUsers' => $recentUsers,
            'recentLogs' => $recentLogs,
        ]);
    }
}
