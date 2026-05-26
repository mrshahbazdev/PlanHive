<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function index(Request $request): Response
    {
        $notes = $request->user()->notes()
            ->with('project:id,name,color')
            ->orderByDesc('is_pinned')
            ->latest()
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->paginate(20);

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Notes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'is_pinned' => ['sometimes', 'boolean'],
        ]);

        $validated['user_id'] = $request->user()->id;
        Note::create($validated);

        return redirect()->route('notes.index')
            ->with('success', __('notes.created'));
    }

    public function show(Note $note): Response
    {
        return Inertia::render('Notes/Show', [
            'note' => $note->load('project:id,name,color', 'documents'),
        ]);
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'is_pinned' => ['sometimes', 'boolean'],
        ]);

        $note->update($validated);

        return back()->with('success', __('notes.updated'));
    }

    public function destroy(Note $note)
    {
        $note->delete();
        return redirect()->route('notes.index')
            ->with('success', __('notes.deleted'));
    }
}
