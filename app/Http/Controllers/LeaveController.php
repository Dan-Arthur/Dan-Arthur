<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view leave'), 403);

        $schoolId = auth()->user()->school_id;

        $query = LeaveRequest::where('school_id', $schoolId)
            ->with(['employee', 'leaveType', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->integer('leave_type_id'));
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->whereHas('employee', fn($q) => $q
                ->where('first_name', 'like', $s)
                ->orWhere('last_name', 'like', $s)
                ->orWhere('employee_number', 'like', $s));
        }

        $requests = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        $leaveTypes = LeaveType::where('school_id', $schoolId)->orderBy('name')->get();
        $statuses   = LeaveRequest::STATUSES;

        // Summary counts for quick overview
        $summary = LeaveRequest::where('school_id', $schoolId)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('leave.index', compact('requests', 'leaveTypes', 'statuses', 'summary'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage leave'), 403);

        $schoolId   = auth()->user()->school_id;
        $employees  = Employee::where('school_id', $schoolId)
            ->where('status', 'active')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();
        $leaveTypes = LeaveType::where('school_id', $schoolId)->orderBy('name')->get();

        return view('leave.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage leave'), 403);

        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:1000',
        ]);

        $schoolId = auth()->user()->school_id;

        $employee = Employee::where('school_id', $schoolId)->findOrFail($validated['employee_id']);

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end   = \Carbon\Carbon::parse($validated['end_date']);
        $days  = $start->diffInWeekdays($end) + 1;

        $leaveType = LeaveType::find($validated['leave_type_id']);
        $status = $leaveType->requires_approval ? 'pending' : 'approved';

        LeaveRequest::create([
            'school_id'     => $schoolId,
            'employee_id'   => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'start_date'    => $validated['start_date'],
            'end_date'      => $validated['end_date'],
            'days_requested'=> $days,
            'reason'        => $validated['reason'] ?? null,
            'status'        => $status,
        ]);

        return redirect()->route('leave.index')->with('success', 'Leave request submitted.');
    }

    public function show(LeaveRequest $leave): View
    {
        abort_unless(auth()->user()->can('view leave'), 403);
        abort_unless($leave->school_id == auth()->user()->school_id, 403);

        $leave->load(['employee.position', 'leaveType', 'approvedBy']);

        return view('leave.show', compact('leave'));
    }

    public function approve(LeaveRequest $leave): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve leave'), 403);
        abort_unless($leave->school_id == auth()->user()->school_id, 403);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'actioned_at' => now(),
            'action_note' => request('action_note'),
        ]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(LeaveRequest $leave): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve leave'), 403);
        abort_unless($leave->school_id == auth()->user()->school_id, 403);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $leave->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'actioned_at' => now(),
            'action_note' => request('action_note'),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }

    public function cancel(LeaveRequest $leave): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage leave'), 403);
        abort_unless($leave->school_id == auth()->user()->school_id, 403);

        if (!in_array($leave->status, ['pending', 'approved'])) {
            return back()->with('error', 'This request cannot be cancelled.');
        }

        $leave->update(['status' => 'cancelled']);

        return back()->with('success', 'Leave request cancelled.');
    }

    // ============================================================
    // LEAVE TYPES
    // ============================================================

    public function types(): View
    {
        abort_unless(auth()->user()->can('manage leave'), 403);

        $schoolId   = auth()->user()->school_id;
        $leaveTypes = LeaveType::where('school_id', $schoolId)
            ->withCount('requests')
            ->orderBy('name')
            ->get();

        return view('leave.types', compact('leaveTypes'));
    }

    public function storeType(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage leave'), 403);

        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'days_allowed'      => 'required|integer|min:0',
            'is_paid'           => 'boolean',
            'requires_approval' => 'boolean',
        ]);

        LeaveType::create(array_merge($validated, [
            'school_id'         => auth()->user()->school_id,
            'is_paid'           => $request->boolean('is_paid'),
            'requires_approval' => $request->boolean('requires_approval'),
        ]));

        return redirect()->route('leave.types')->with('success', 'Leave type added.');
    }

    public function updateType(Request $request, LeaveType $leaveType): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage leave'), 403);
        abort_unless($leaveType->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'days_allowed'      => 'required|integer|min:0',
            'is_paid'           => 'boolean',
            'requires_approval' => 'boolean',
        ]);

        $leaveType->update([
            'name'              => $validated['name'],
            'days_allowed'      => $validated['days_allowed'],
            'is_paid'           => $request->boolean('is_paid'),
            'requires_approval' => $request->boolean('requires_approval'),
        ]);

        return redirect()->route('leave.types')->with('success', 'Leave type updated.');
    }

    public function destroyType(LeaveType $leaveType): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage leave'), 403);
        abort_unless($leaveType->school_id == auth()->user()->school_id, 403);

        if ($leaveType->requests()->exists()) {
            return back()->with('error', 'Cannot delete a leave type that has requests linked to it.');
        }

        $leaveType->delete();

        return redirect()->route('leave.types')->with('success', 'Leave type deleted.');
    }
}
