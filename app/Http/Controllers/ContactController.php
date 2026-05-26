<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $contacts = $request->user()->contacts()
            ->with('project:id,name,color', 'tags')
            ->when($request->search, fn ($q, $search) =>
                $q->where(fn ($q) =>
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                )
            )
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $request->only(['search', 'project_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'source' => ['sometimes', 'in:manual,business_card,outlook,import'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:50'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $contact = Contact::create($validated);

        foreach ($tags as $tag) {
            $contact->tags()->create(['tag' => $tag]);
        }

        return back()->with('success', __('contacts.created'));
    }

    public function show(Contact $contact): Response
    {
        return Inertia::render('Contacts/Show', [
            'contact' => $contact->load('project:id,name,color', 'tags', 'documents'),
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $contact->update($validated);

        return back()->with('success', __('contacts.updated'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')
            ->with('success', __('contacts.deleted'));
    }
}
