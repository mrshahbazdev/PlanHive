<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function index(Request $request): Response
    {
        $tokens = $request->user()->tokens()->latest()->get()->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]);

        return Inertia::render('Api/Index', [
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'array',
            'abilities.*' => 'string|in:projects:read,projects:write,tasks:read,tasks:write,contacts:read,contacts:write',
        ]);

        $token = $request->user()->createToken(
            $validated['name'],
            $validated['abilities'] ?? ['*']
        );

        return back()->with('success', 'API token created.')->with('token', $token->plainTextToken);
    }

    public function destroy(Request $request, string $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return back()->with('success', 'Token revoked.');
    }
}
