<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('system.view_logs');

        $query = AuditLog::with('user')
            ->latest();

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }

        // Filter by critical status
        if ($request->has('critical') && $request->critical === '1') {
            $query->where('is_critical', true);
        }

        // Date range filters
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(50);
        $users = User::whereIn('id', AuditLog::distinct()->pluck('user_id'))->get();

        return view('admin.audit_logs.index', compact('logs', 'users'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('system.view_logs');

        return view('admin.audit_logs.show', compact('auditLog'));
    }

    public function userActivity(User $user, Request $request)
    {
        $this->authorize('system.view_logs');

        $logs = AuditLog::where('user_id', $user->id)
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('admin.audit_logs.user', compact('logs', 'user'));
    }

    public function resourceActivity($type, $id)
    {
        $this->authorize('system.view_logs');

        $logs = AuditLog::where('resource_type', $type)
            ->where('resource_id', $id)
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('admin.audit_logs.resource', compact('logs', 'type', 'id'));
    }

    public function export(Request $request)
    {
        $this->authorize('system.view_logs');

        $query = AuditLog::with('user');

        // Apply same filters as index
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->latest()->get();

        $filename = 'audit_logs_'.now()->format('Y-m-d_H-i-s').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Action', 'Resource Type', 'Resource ID', 'IP Address', 'Critical']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'System',
                    $log->action,
                    $log->resource_type ?? 'N/A',
                    $log->resource_id ?? 'N/A',
                    $log->ip_address ?? 'N/A',
                    $log->is_critical ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
