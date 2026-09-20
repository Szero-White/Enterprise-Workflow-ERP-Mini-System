<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $rules = [
            'action' => ['nullable', 'string', 'max:100'],
            'actor_id' => ['nullable', 'integer', 'exists:users,id'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d'],
        ];

        if ($request->filled('from_date')) {
            $rules['to_date'][] = 'after_or_equal:from_date';
        }

        $filters = $request->validate($rules);

        $query = AuditLog::query()
            ->with('actor')
            ->latest('id');

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['actor_id'])) {
            $query->where('actor_id', $filters['actor_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $logs = $query->paginate(15)->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $actors = User::query()
            ->whereHas('auditLogs')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('admin.audit_logs.index', compact('logs', 'actions', 'actors', 'filters'));
    }
}
