<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\FeedingEnrollment;
use App\Models\FeedingRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeedingController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================

    public function index(): View
    {
        abort_unless(auth()->user()->can('view feeding'), 403);

        $schoolId    = auth()->user()->school_id;
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $today       = Carbon::today()->toDateString();

        $enrolledCount = FeedingEnrollment::where('school_id', $schoolId)
            ->where('status', 'active')
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->count();

        $fedToday = FeedingRecord::where('school_id', $schoolId)
            ->where('record_date', $today)
            ->where('fed', 1)
            ->count();

        $recordedToday = FeedingRecord::where('school_id', $schoolId)
            ->where('record_date', $today)
            ->exists();

        $absentToday = $recordedToday ? ($enrolledCount - $fedToday) : null;

        $recentDates = FeedingRecord::where('school_id', $schoolId)
            ->selectRaw("record_date, SUM(CASE WHEN fed = 1 THEN 1 ELSE 0 END) as fed_count, COUNT(*) as total")
            ->groupBy('record_date')
            ->orderByDesc('record_date')
            ->limit(10)
            ->get();

        return view('feeding.index', compact(
            'enrolledCount', 'fedToday', 'absentToday',
            'recordedToday', 'recentDates', 'currentYear', 'today'
        ));
    }

    // ============================================================
    // ENROLLMENT
    // ============================================================

    public function enrollment(Request $request): View
    {
        abort_unless(auth()->user()->can('view feeding'), 403);

        $schoolId    = auth()->user()->school_id;
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $classes     = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $query = FeedingEnrollment::where('school_id', $schoolId)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->with(['student.currentClass']);

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn($q) => $q->where('current_class_id', $request->integer('class_id')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->get()->sortBy(
            fn($e) => sprintf('%s|%s|%s',
                str_pad($e->student->current_class_id ?? 9999, 6, '0', STR_PAD_LEFT),
                $e->student->last_name,
                $e->student->first_name
            )
        );

        $enrolledStudentIds = FeedingEnrollment::where('school_id', $schoolId)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->pluck('student_id');

        $availableStudents = Student::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereNotIn('id', $enrolledStudentIds)
            ->with('currentClass')
            ->orderBy('current_class_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->groupBy(fn($s) => $s->currentClass?->name ?? 'No Class');

        return view('feeding.enrollment', compact(
            'enrollments', 'availableStudents', 'classes', 'currentYear'
        ));
    }

    public function enroll(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage feeding'), 403);

        $validated = $request->validate([
            'student_ids'      => 'required|array|min:1',
            'student_ids.*'    => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $schoolId = auth()->user()->school_id;
        $count    = 0;

        foreach ($validated['student_ids'] as $studentId) {
            FeedingEnrollment::firstOrCreate(
                [
                    'school_id'        => $schoolId,
                    'student_id'       => $studentId,
                    'academic_year_id' => $validated['academic_year_id'],
                ],
                [
                    'enrolled_date' => today()->toDateString(),
                    'status'        => 'active',
                ]
            );
            $count++;
        }

        return redirect()->route('feeding.enrollment')
            ->with('success', "{$count} student(s) enrolled in the feeding program.");
    }

    public function updateEnrollment(Request $request, FeedingEnrollment $enrollment): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage feeding'), 403);
        abort_unless($enrollment->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'status' => 'required|in:active,suspended,withdrawn',
            'notes'  => 'nullable|string|max:500',
        ]);

        $enrollment->update($validated);

        return back()->with('success', 'Enrollment status updated.');
    }

    public function unenroll(FeedingEnrollment $enrollment): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage feeding'), 403);
        abort_unless($enrollment->school_id == auth()->user()->school_id, 403);

        $enrollment->update(['status' => 'withdrawn']);

        return back()->with('success', 'Student removed from feeding program.');
    }

    // ============================================================
    // DAILY RECORDS
    // ============================================================

    public function records(Request $request): View
    {
        abort_unless(auth()->user()->can('view feeding'), 403);

        $schoolId    = auth()->user()->school_id;
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();

        $date = $request->filled('date')
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::today()->toDateString();

        $enrollments = FeedingEnrollment::where('school_id', $schoolId)
            ->where('status', 'active')
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->with(['student.currentClass'])
            ->get()
            ->sortBy(fn($e) => sprintf('%s|%s|%s',
                str_pad($e->student->current_class_id ?? 9999, 6, '0', STR_PAD_LEFT),
                $e->student->last_name,
                $e->student->first_name
            ));

        $records = FeedingRecord::where('school_id', $schoolId)
            ->where('record_date', $date)
            ->pluck('fed', 'student_id')
            ->toArray();

        $savedForDate = FeedingRecord::where('school_id', $schoolId)
            ->where('record_date', $date)
            ->exists();

        $byClass = $enrollments->groupBy(fn($e) => $e->student->currentClass?->name ?? 'No Class');

        return view('feeding.records', compact('byClass', 'records', 'date', 'savedForDate'));
    }

    public function saveRecords(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('record feeding'), 403);

        $validated = $request->validate([
            'record_date' => 'required|date',
            'fed'         => 'nullable|array',
            'fed.*'       => 'integer',
        ]);

        $schoolId    = auth()->user()->school_id;
        $date        = $validated['record_date'];
        $fedIds      = collect($validated['fed'] ?? []);
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();

        $enrolledStudentIds = FeedingEnrollment::where('school_id', $schoolId)
            ->where('status', 'active')
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->pluck('student_id');

        DB::transaction(function () use ($schoolId, $date, $fedIds, $enrolledStudentIds) {
            FeedingRecord::where('school_id', $schoolId)->where('record_date', $date)->delete();

            foreach ($enrolledStudentIds as $studentId) {
                FeedingRecord::create([
                    'school_id'   => $schoolId,
                    'student_id'  => $studentId,
                    'record_date' => $date,
                    'fed'         => $fedIds->contains($studentId),
                ]);
            }
        });

        return back()->with('success', 'Feeding records saved for ' . Carbon::parse($date)->format('d M Y') . '.');
    }
}
