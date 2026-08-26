<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrolment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view attendance'), 403);

        $schoolId = auth()->user()->school_id;

        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $currentYear  = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedDate = $request->get('date', now()->toDateString());
        $selectedYearId  = $request->integer('year_id', $currentYear?->id ?? 0);
        $selectedClassId = $request->integer('class_id', $classes->first()?->id ?? 0);

        $attendance = collect();
        $students   = collect();
        $taken      = false;

        if ($selectedClassId && $selectedYearId) {
            $attendance = StudentAttendance::where('class_id', $selectedClassId)
                ->whereDate('date', $selectedDate)
                ->where('academic_year_id', $selectedYearId)
                ->with('student')
                ->get()
                ->keyBy('student_id');

            $taken = $attendance->isNotEmpty();

            // Active enrolments for this class/year
            $students = Enrolment::where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedYearId)
                ->where('status', 'active')
                ->with('student')
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy('last_name')
                ->values();
        }

        // Summary: last 7 days for selected class
        $summary = [];
        if ($selectedClassId && $selectedYearId) {
            $summary = StudentAttendance::where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedYearId)
                ->where('date', '>=', now()->subDays(6)->toDateString())
                ->select('date', 'status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get()
                ->groupBy('date');
        }

        return view('attendance.index', compact(
            'years', 'classes', 'attendance', 'students', 'taken',
            'selectedDate', 'selectedYearId', 'selectedClassId', 'summary',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('take attendance'), 403);

        $request->validate([
            'class_id'         => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'date'             => 'required|date',
            'attendance'       => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status'     => 'required|in:present,absent,late,excused',
        ]);

        $schoolId = auth()->user()->school_id;
        $classId  = $request->input('class_id');
        $yearId   = $request->input('academic_year_id');
        $date     = $request->input('date');

        $termId = Term::where('academic_year_id', $yearId)
            ->where('is_current', true)
            ->value('id');

        DB::transaction(function () use ($request, $schoolId, $classId, $yearId, $date, $termId) {
            foreach ($request->input('attendance') as $row) {
                StudentAttendance::updateOrCreate(
                    ['student_id' => $row['student_id'], 'date' => $date, 'class_id' => $classId],
                    [
                        'school_id'        => $schoolId,
                        'academic_year_id' => $yearId,
                        'term_id'          => $termId,
                        'status'           => $row['status'],
                        'arrival_time'     => $row['arrival_time'] ?? null,
                        'reason'           => $row['reason'] ?? null,
                        'taken_by'         => auth()->id(),
                        'method'           => 'manual',
                    ]
                );
            }
        });

        return redirect()->route('attendance.index', [
            'class_id' => $classId,
            'year_id'  => $yearId,
            'date'     => $date,
        ])->with('success', 'Attendance saved for ' . date('d M Y', strtotime($date)) . '.');
    }

    public function report(Request $request): View
    {
        abort_unless(auth()->user()->can('view attendance'), 403);

        $schoolId = auth()->user()->school_id;

        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId  = $request->integer('year_id', $currentYear?->id ?? 0);
        $selectedClassId = $request->integer('class_id');

        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $report = collect();
        $totalDays = 0;

        if ($selectedClassId && $selectedYearId) {
            $totalDays = StudentAttendance::where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedYearId)
                ->whereBetween('date', [$from, $to])
                ->select('date')
                ->distinct()
                ->count();

            $report = StudentAttendance::where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedYearId)
                ->whereBetween('date', [$from, $to])
                ->select('student_id', 'status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('student_id', 'status')
                ->with('student')
                ->get()
                ->groupBy('student_id');
        }

        return view('attendance.report', compact(
            'years', 'classes', 'report', 'totalDays',
            'selectedYearId', 'selectedClassId', 'from', 'to',
        ));
    }
}
