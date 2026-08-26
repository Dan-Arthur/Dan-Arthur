<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view timetables'), 403);

        $schoolId = auth()->user()->school_id;

        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $periods = TimetablePeriod::where('school_id', $schoolId)->orderBy('sort_order')->get();

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId = $request->integer('year_id', $currentYear?->id ?? 0);
        $selectedClassId = $request->integer('class_id', $classes->first()?->id ?? 0);

        $grid    = [];
        $entries = collect();

        if ($selectedClassId && $selectedYearId) {
            $entries = Timetable::where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedYearId)
                ->where('is_active', true)
                ->with(['subject', 'teacher', 'period'])
                ->get();

            foreach ($entries as $entry) {
                $grid[$entry->period_id][$entry->day_of_week] = $entry;
            }
        }

        $activeDays = $this->activeDays($entries);
        $teachers   = $this->teacherList($schoolId);
        $subjects   = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('timetables.index', compact(
            'years', 'classes', 'periods', 'grid', 'activeDays',
            'selectedYearId', 'selectedClassId', 'teachers', 'subjects',
        ));
    }

    public function teacher(Request $request): View
    {
        abort_unless(auth()->user()->can('view timetables'), 403);

        $schoolId = auth()->user()->school_id;
        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $periods = TimetablePeriod::where('school_id', $schoolId)->orderBy('sort_order')->get();
        $teachers = $this->teacherList($schoolId);
        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId    = $request->integer('year_id', $currentYear?->id ?? 0);
        $selectedTeacherId = $request->integer('teacher_id', $teachers->first()?->id ?? 0);

        $grid    = [];
        $entries = collect();

        if ($selectedTeacherId && $selectedYearId) {
            $entries = Timetable::where('teacher_id', $selectedTeacherId)
                ->where('academic_year_id', $selectedYearId)
                ->where('is_active', true)
                ->with(['subject', 'schoolClass', 'period'])
                ->get();

            foreach ($entries as $entry) {
                $grid[$entry->period_id][$entry->day_of_week] = $entry;
            }
        }

        $activeDays = $this->activeDays($entries);

        return view('timetables.teacher', compact(
            'years', 'classes', 'periods', 'grid', 'activeDays',
            'selectedYearId', 'selectedTeacherId', 'teachers', 'subjects',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);

        $validated = $request->validate([
            'class_id'        => 'required|exists:school_classes,id',
            'subject_id'      => 'required|exists:subjects,id',
            'teacher_id'      => 'nullable|exists:users,id',
            'period_id'       => 'required|exists:timetable_periods,id',
            'academic_year_id'=> 'required|exists:academic_years,id',
            'term_id'         => 'nullable|exists:terms,id',
            'day_of_week'     => 'required|integer|between:1,7',
            'room'            => 'nullable|string|max:100',
        ]);

        // Check for conflicts: same class + same period + same day
        $conflict = Timetable::where('class_id', $validated['class_id'])
            ->where('period_id', $validated['period_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('is_active', true)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'This class already has a subject in that period/day slot.')->withInput();
        }

        // Teacher conflict check
        if (!empty($validated['teacher_id'])) {
            $teacherConflict = Timetable::where('teacher_id', $validated['teacher_id'])
                ->where('period_id', $validated['period_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('is_active', true)
                ->exists();

            if ($teacherConflict) {
                return back()->with('error', 'This teacher is already assigned in that period/day slot.')->withInput();
            }
        }

        $validated['is_active'] = true;
        Timetable::create($validated);

        return back()->with('success', 'Slot added to timetable.');
    }

    public function update(Request $request, Timetable $timetable): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);

        $validated = $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'teacher_id'  => 'nullable|exists:users,id',
            'room'        => 'nullable|string|max:100',
        ]);

        $timetable->update($validated);

        return back()->with('success', 'Timetable slot updated.');
    }

    public function destroy(Timetable $timetable): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);

        $timetable->delete();

        return back()->with('success', 'Slot removed from timetable.');
    }

    // ============================================================
    // PERIOD MANAGEMENT
    // ============================================================

    public function periods(): View
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);

        $schoolId = auth()->user()->school_id;
        $periods  = TimetablePeriod::where('school_id', $schoolId)->orderBy('sort_order')->get();

        return view('timetables.periods', compact('periods'));
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);

        $validated = $request->validate([
            'name'       => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_break'   => 'boolean',
            'sort_order' => 'required|integer|min:1',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['is_break']  = $request->boolean('is_break');

        TimetablePeriod::create($validated);

        return redirect()->route('timetables.periods')->with('success', 'Period added.');
    }

    public function updatePeriod(Request $request, TimetablePeriod $period): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);
        abort_unless($period->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name'       => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_break'   => 'boolean',
            'sort_order' => 'required|integer|min:1',
        ]);

        $validated['is_break'] = $request->boolean('is_break');
        $period->update($validated);

        return redirect()->route('timetables.periods')->with('success', 'Period updated.');
    }

    public function destroyPeriod(TimetablePeriod $period): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage timetables'), 403);
        abort_unless($period->school_id == auth()->user()->school_id, 403);

        if ($period->timetables()->exists()) {
            return back()->with('error', 'Cannot delete period that is used in timetable slots.');
        }

        $period->delete();

        return redirect()->route('timetables.periods')->with('success', 'Period deleted.');
    }

    // ============================================================
    // Helpers
    // ============================================================

    private function activeDays($entries): array
    {
        $days = $entries->pluck('day_of_week')->unique()->sort()->values()->toArray();
        // Default to Mon-Fri if no entries
        return $days ?: [1, 2, 3, 4, 5];
    }

    private function teacherList(int $schoolId)
    {
        return User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', [
                'teacher', 'principal', 'vice-principal', 'school-admin',
            ]))
            ->orderBy('name')
            ->get();
    }
}
