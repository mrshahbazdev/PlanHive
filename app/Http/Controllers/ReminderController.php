<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Task;
use App\Models\Goal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $reminders = $user->reminders()
            ->with('remindable')
            ->orderBy('remind_at')
            ->paginate(20);

        $projectIds = $user->projects()->pluck('projects.id')
            ->merge($user->ownedProjects()->pluck('id'));

        $tasks = Task::where(function ($q) use ($user, $projectIds) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id)
                  ->orWhereIn('project_id', $projectIds);
            })
            ->whereNotIn('status', ['done', 'cancelled'])
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        $goals = Goal::whereIn('project_id', $projectIds)
            ->whereNotIn('status', ['achieved', 'missed'])
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return Inertia::render('Reminders/Index', [
            'reminders' => $reminders,
            'tasks' => $tasks,
            'goals' => $goals,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'remind_at' => ['required', 'date', 'after:now'],
            'channel' => ['required', 'in:in_app,email,push,teams'],
            'recurrence' => ['nullable', 'in:daily,weekly,monthly'],
            'remindable_type' => ['nullable', 'string', 'in:task,goal'],
            'remindable_id' => ['nullable', 'integer'],
        ]);

        $validated['user_id'] = $request->user()->id;

        if (!empty($validated['remindable_type'])) {
            $validated['remindable_type'] = $validated['remindable_type'] === 'task'
                ? Task::class
                : Goal::class;
        }

        Reminder::create($validated);

        return back()->with('success', 'Reminder created successfully');
    }

    public function destroy(Reminder $reminder)
    {
        $reminder->delete();
        return back()->with('success', 'Reminder deleted');
    }
}
