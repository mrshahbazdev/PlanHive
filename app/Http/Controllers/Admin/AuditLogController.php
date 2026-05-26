<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user:id,name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(30)->withQueryString();

        $actions = AuditLog::distinct()->pluck('action')->sort()->values();

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $request->only(['search', 'action']),
        ]);
    }
}
