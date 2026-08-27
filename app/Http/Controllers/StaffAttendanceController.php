<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\StaffAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaffAttendanceController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view staff attendance'), 403);

        $schoolId = $this->schoolId();

        $departments = Department::where('school_id', $schoolId)->orderBy('name')->get();

        $selectedDate    = $request->get('date', now()->toDateString());
        $selectedDeptId  = $request->integer('department_id');

        $employeeQuery = Employee::where('school_id', $schoolId)
            ->where('status', 'active')
            ->with(['user', 'department', 'position'])
            ->when($selectedDeptId, fn($q) => $q->where('department_id', $selectedDeptId))
            ->orderBy('first_name');

        $employees = $employeeQuery->get();

        // Existing attendance records for this date keyed by user_id
        $attendance = StaffAttendance::where('school_id', $schoolId)
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('user_id');

        $taken = $attendance->isNotEmpty();

        // 7-day summary for mini calendar
        $summary = StaffAttendance::where('school_id', $schoolId)
            ->where('date', '>=', now()->subDays(6)->toDateString())
            ->select('date', 'status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('staff-attendance.index', compact(
            'employees', 'departments', 'attendance', 'taken',
            'selectedDate', 'selectedDeptId', 'summary',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage staff attendance'), 403);

        $request->validate([
            'date'                      => 'required|date',
            'attendance'                => 'required|array',
            'attendance.*.user_id'      => 'required|exists:users,id',
            'attendance.*.status'       => 'required|in:' . implode(',', array_keys(StaffAttendance::STATUSES)),
            'attendance.*.check_in'     => 'nullable|date_format:H:i',
            'attendance.*.check_out'    => 'nullable|date_format:H:i',
            'attendance.*.reason'       => 'nullable|string|max:255',
        ]);

        $schoolId = $this->schoolId();
        $date     = $request->input('date');

        DB::transaction(function () use ($request, $schoolId, $date) {
            foreach ($request->input('attendance') as $row) {
                StaffAttendance::updateOrCreate(
                    ['school_id' => $schoolId, 'user_id' => $row['user_id'], 'date' => $date],
                    [
                        'status'    => $row['status'],
                        'check_in'  => $row['check_in']  ?: null,
                        'check_out' => $row['check_out'] ?: null,
                        'reason'    => $row['reason']    ?: null,
                        'method'    => 'manual',
                    ]
                );
            }
        });

        return redirect()->route('staff-attendance.index', [
            'date' => $date,
            'department_id' => $request->input('department_id'),
        ])->with('success', 'Staff attendance saved for ' . date('d M Y', strtotime($date)) . '.');
    }

    public function report(Request $request): View
    {
        abort_unless(auth()->user()->can('view staff attendance'), 403);

        $schoolId = $this->schoolId();

        $departments    = Department::where('school_id', $schoolId)->orderBy('name')->get();
        $selectedDeptId = $request->integer('department_id');
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $employeeQuery = Employee::where('school_id', $schoolId)
            ->where('status', 'active')
            ->with(['user', 'department', 'position'])
            ->when($selectedDeptId, fn($q) => $q->where('department_id', $selectedDeptId))
            ->orderBy('first_name');

        $employees = $employeeQuery->get();

        // Total working days in period (days where at least one record exists)
        $totalDays = StaffAttendance::where('school_id', $schoolId)
            ->whereBetween('date', [$from, $to])
            ->select('date')
            ->distinct()
            ->count();

        // Attendance counts per user_id per status
        $rawCounts = StaffAttendance::where('school_id', $schoolId)
            ->whereBetween('date', [$from, $to])
            ->select('user_id', 'status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id', 'status')
            ->get()
            ->groupBy('user_id');

        return view('staff-attendance.report', compact(
            'employees', 'departments', 'rawCounts', 'totalDays',
            'selectedDeptId', 'from', 'to',
        ));
    }
}
