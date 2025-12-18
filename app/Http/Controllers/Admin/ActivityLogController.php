<?php
// app/Http/Controllers/Admin/ActivityLogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by user
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('log_type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name', 'role']);
        $logTypes = ActivityLog::distinct()->pluck('log_type');

        return view('admin.activity-logs.index', compact('logs', 'users', 'logTypes'));
    }

    /**
     * Show log details
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');

        return view('admin.activity-logs.show', compact('activityLog'));
    }

    /**
     * Clear old logs
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
        ]);

        $date = now()->subDays($request->days);
        $count = ActivityLog::where('created_at', '<', $date)->count();

        ActivityLog::where('created_at', '<', $date)->delete();

        return back()->with('success', "{$count} old activity logs deleted.");
    }

    /**
     * Export logs
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $logs = $query->latest()->get();

        $csv = "ID,User,Type,Description,IP Address,Date\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                '"' . ($log->user?->name ?? 'System') . '"',
                $log->log_type,
                '"' . str_replace('"', '""', $log->description) . '"',
                $log->ip_address ?? '',
                $log->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=activity_logs_' . now()->format('Y-m-d') . '.csv');
    }
}