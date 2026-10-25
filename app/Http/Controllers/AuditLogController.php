<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $viewerIsSuperAdmin = (bool) auth()->user()?->is_super_admin;

        $logs = AuditLog::with('user')
            // The hidden super admin's activity is only visible to the super admin.
            ->when(! $viewerIsSuperAdmin, fn ($q) => $q->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereHas('user', fn ($u) => $u->where('is_super_admin', false));
            }))
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->action, fn ($q, $v) => $q->where('action', $v))
            ->when($request->model, fn ($q, $v) => $q->where('auditable_type', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('created_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $users   = User::visible()->orderBy('name')->get(['id', 'name']);
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action');
        $models  = AuditLog::whereNotNull('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        return view('audit-logs.index', compact('logs', 'users', 'actions', 'models'));
    }
}