<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view audit logs'), 403);

        $schoolId = auth()->user()->school_id;

        $query = AuditLog::where('school_id', $schoolId)
            ->with('user')
            ->orderByDesc('created_at');

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('model')) {
            // Match short class name — e.g. "Student" matches "App\Models\Student"
            $query->where('auditable_type', 'like', '%\\' . $request->model);
        }

        if ($request->filled('tags')) {
            $query->where('tags', 'like', '%' . $request->tags . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs  = $query->paginate(50)->withQueryString();
        $users = User::where('school_id', $schoolId)->orderBy('name')->get(['id', 'name']);

        // Distinct model types for filter
        $modelTypes = AuditLog::where('school_id', $schoolId)
            ->whereNotNull('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->map(fn($t) => class_basename($t))
            ->unique()
            ->sort()
            ->values();

        $events = AuditLog::EVENTS;

        // Summary counts for the current filters
        $summary = AuditLog::where('school_id', $schoolId)
            ->selectRaw('event, COUNT(*) as cnt')
            ->groupBy('event')
            ->pluck('cnt', 'event');

        return view('audit.index', compact('logs', 'users', 'modelTypes', 'events', 'summary'));
    }

    public function show(AuditLog $audit): View
    {
        abort_unless(auth()->user()->can('view audit logs'), 403);
        abort_unless($audit->school_id == auth()->user()->school_id, 403);

        $audit->load('user');

        return view('audit.show', compact('audit'));
    }
}
