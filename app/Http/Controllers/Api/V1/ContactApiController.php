<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contacts = $request->user()->contacts()
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate($request->input('per_page', 20));

        return response()->json($contacts);
    }

    public function show(Request $request, Contact $contact): JsonResponse
    {
        if ($contact->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json(['data' => $contact]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;
        $contact = Contact::create($validated);

        return response()->json(['data' => $contact], 201);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        if ($contact->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        return response()->json(['data' => $contact]);
    }

    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        if ($contact->user_id !== $request->user()->id) {
            abort(403);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact deleted']);
    }
}
