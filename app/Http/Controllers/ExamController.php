<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view exams'), 403);

        $schoolId    = auth()->user()->school_id;
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $yearId      = $request->get('year_id', $currentYear?->id);

        $exams = Exam::where('school_id', $schoolId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->when($request->term_id, fn($q) => $q->where('term_id', $request->term_id))
            ->when($request->class_id, fn($q) => $q->where('school_class_id', $request->class_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['academicYear', 'term', 'schoolClass', 'subject'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->paginate(25)
            ->withQueryString();

        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms   = $yearId ? Term::where('academic_year_id', $yearId)->orderBy('sequence')->get() : collect();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('exams.index', compact('exams', 'years', 'terms', 'classes', 'yearId', 'currentYear'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage exams'), 403);

        $schoolId    = auth()->user()->school_id;
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms       = $currentYear ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get() : collect();
        $classes     = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $subjects    = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('exams.create', compact('years', 'terms', 'classes', 'subjects', 'currentYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage exams'), 403);

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'nullable|exists:terms,id',
            'school_class_id'  => 'nullable|exists:school_classes,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'title'            => 'required|string|max:200',
            'exam_date'        => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'venue'            => 'nullable|string|max:200',
            'invigilator'      => 'nullable|string|max:200',
            'status'           => 'required|in:scheduled,ongoing,completed,cancelled',
            'notes'            => 'nullable|string|max:1000',
        ]);

        Exam::create(array_merge($validated, ['school_id' => auth()->user()->school_id]));

        return redirect()->route('exams.index')->with('success', 'Exam scheduled successfully.');
    }

    public function edit(Exam $exam): View
    {
        abort_unless(auth()->user()->can('manage exams'), 403);
        abort_unless($exam->school_id == auth()->user()->school_id, 403);

        $schoolId = auth()->user()->school_id;
        $years    = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms    = Term::where('academic_year_id', $exam->academic_year_id)->orderBy('sequence')->get();
        $classes  = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('exams.edit', compact('exam', 'years', 'terms', 'classes', 'subjects'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage exams'), 403);
        abort_unless($exam->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'nullable|exists:terms,id',
            'school_class_id'  => 'nullable|exists:school_classes,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'title'            => 'required|string|max:200',
            'exam_date'        => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'venue'            => 'nullable|string|max:200',
            'invigilator'      => 'nullable|string|max:200',
            'status'           => 'required|in:scheduled,ongoing,completed,cancelled',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $exam->update($validated);

        return redirect()->route('exams.index')->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage exams'), 403);
        abort_unless($exam->school_id == auth()->user()->school_id, 403);

        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    public function termsByYear(Request $request)
    {
        $terms = Term::where('academic_year_id', $request->year_id)->orderBy('sequence')->get(['id', 'name']);
        return response()->json($terms);
    }
}
