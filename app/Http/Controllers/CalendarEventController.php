<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $events = $request->user()->calendarEvents()
            ->with('project:id,name,color')
            ->when($request->start, fn ($q) => $q->where('end_at', '>=', $request->start))
            ->when($request->end, fn ($q) => $q->where('start_at', '<=', $request->end))
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
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

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'all_day' => ['sometimes', 'boolean'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'color_override' => ['nullable', 'string', 'max:7'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $event = CalendarEvent::create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date'],
            'all_day' => ['sometimes', 'boolean'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'color_override' => ['nullable', 'string', 'max:7'],
        ]);

        $calendarEvent->update($validated);

        return response()->json($calendarEvent);
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $calendarEvent->delete();
        return response()->json(null, 204);
    }
}
