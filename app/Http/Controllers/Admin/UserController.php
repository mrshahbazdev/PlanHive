<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::withCount(['ownedProjects', 'tasks', 'notes']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->input('role') === 'admin') {
            $query->where('is_admin', true);
        } elseif ($request->input('role') === 'user') {
            $query->where('is_admin', false);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function show(User $user): Response
    {
        $user->loadCount(['ownedProjects', 'tasks', 'notes', 'contacts', 'reminders']);

        $recentActivity = AuditLog::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'is_admin' => ['boolean'],
        ]);

        $oldValues = $user->only(['name', 'email', 'is_admin']);
        $user->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user.updated',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user.deleted',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $user->only(['name', 'email']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleAdmin(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $user->is_admin ? 'user.promoted_admin' : 'user.demoted_admin',
            'model_type' => 'User',
            'model_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', $user->is_admin ? 'User promoted to admin.' : 'Admin role removed.');
    }
}
