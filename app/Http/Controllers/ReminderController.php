<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function index(Request $request): Response
    {
        $reminders = $request->user()->reminders()
            ->orderBy('remind_at')
            ->paginate(20);

        return Inertia::render('Reminders/Index', [
            'reminders' => $reminders,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'remind_at' => ['required', 'date', 'after:now'],
            'channel' => ['required', 'in:in_app,email,push,teams'],
            'remindable_type' => ['nullable', 'string'],
            'remindable_id' => ['nullable', 'integer'],
        ]);

        $validated['user_id'] = $request->user()->id;
        Reminder::create($validated);

        return back()->with('success', 'Reminder created successfully');
    }

    public function destroy(Reminder $reminder)
    {
        $reminder->delete();
        return back()->with('success', 'Reminder deleted');
    }
}
