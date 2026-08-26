<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Enrolment;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view assessments'), 403);

        $schoolId = auth()->user()->school_id;

        $years   = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $terms   = collect();

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId  = $request->integer('year_id', $currentYear?->id ?? 0);
        $selectedClassId = $request->integer('class_id');

        if ($selectedYearId) {
            $terms = Term::where('academic_year_id', $selectedYearId)->orderBy('sequence')->get();
        }

        $query = Assessment::where('school_id', $schoolId)
            ->with(['schoolClass', 'subject', 'teacher', 'term'])
            ->withCount('marks');

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }
        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }
        if ($termId = $request->integer('term_id')) {
            $query->where('term_id', $termId);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%$search%");
        }

        $assessments = $query->orderByDesc('assessment_date')->paginate(30)->withQueryString();

        return view('assessments.index', compact(
            'assessments', 'years', 'classes', 'terms',
            'selectedYearId', 'selectedClassId',
        ));
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->can('create assessments'), 403);

        $schoolId = auth()->user()->school_id;
        $classes  = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $years    = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms    = collect();
        $teachers = $this->teacherList($schoolId);

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        if ($currentYear) {
            $terms = Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get();
        }

        return view('assessments.create', compact('classes', 'subjects', 'years', 'terms', 'teachers', 'currentYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('create assessments'), 403);

        $validated = $request->validate([
            'class_id'           => 'required|exists:school_classes,id',
            'subject_id'         => 'required|exists:subjects,id',
            'teacher_id'         => 'nullable|exists:users,id',
            'academic_year_id'   => 'required|exists:academic_years,id',
            'term_id'            => 'required|exists:terms,id',
            'title'              => 'required|string|max:200',
            'type'               => 'required|in:' . implode(',', array_keys(Assessment::TYPES)),
            'max_score'          => 'required|numeric|min:1|max:1000',
            'weight'             => 'nullable|numeric|min:0|max:100',
            'assessment_date'    => 'nullable|date',
            'submission_deadline'=> 'nullable|date',
            'description'        => 'nullable|string|max:1000',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status']    = 'draft';

        if (empty($validated['teacher_id'])) {
            $validated['teacher_id'] = auth()->id();
        }

        $assessment = Assessment::create($validated);

        return redirect()->route('assessments.show', $assessment)->with('success', 'Assessment created.');
    }

    public function show(Assessment $assessment): View
    {
        abort_unless(auth()->user()->can('view assessments'), 403);
        $this->authorizeSchool($assessment);

        $assessment->load(['schoolClass', 'subject', 'teacher', 'term', 'academicYear']);

        // Get enrolled students
        $students = Enrolment::where('class_id', $assessment->class_id)
            ->where('academic_year_id', $assessment->academic_year_id)
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter()
            ->sortBy('last_name')
            ->values();

        // Existing marks keyed by student_id
        $marks = $assessment->marks()->with('student')->get()->keyBy('student_id');

        // Stats
        $scores = $marks->filter(fn($m) => !$m->is_absent && !$m->is_exempt && !is_null($m->score))
            ->pluck('score');
        $stats = [
            'count'   => $scores->count(),
            'average' => $scores->avg() ? round($scores->avg(), 2) : null,
            'highest' => $scores->max(),
            'lowest'  => $scores->min(),
            'absent'  => $marks->where('is_absent', true)->count(),
        ];

        return view('assessments.show', compact('assessment', 'students', 'marks', 'stats'));
    }

    public function edit(Assessment $assessment): View
    {
        abort_unless(auth()->user()->can('edit assessments'), 403);
        $this->authorizeSchool($assessment);

        $schoolId = auth()->user()->school_id;
        $classes  = SchoolClass::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $years    = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms    = Term::where('academic_year_id', $assessment->academic_year_id)->orderBy('sequence')->get();
        $teachers = $this->teacherList($schoolId);

        return view('assessments.edit', compact('assessment', 'classes', 'subjects', 'years', 'terms', 'teachers'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        abort_unless(auth()->user()->can('edit assessments'), 403);
        $this->authorizeSchool($assessment);

        $validated = $request->validate([
            'class_id'           => 'required|exists:school_classes,id',
            'subject_id'         => 'required|exists:subjects,id',
            'teacher_id'         => 'nullable|exists:users,id',
            'academic_year_id'   => 'required|exists:academic_years,id',
            'term_id'            => 'required|exists:terms,id',
            'title'              => 'required|string|max:200',
            'type'               => 'required|in:' . implode(',', array_keys(Assessment::TYPES)),
            'max_score'          => 'required|numeric|min:1|max:1000',
            'weight'             => 'nullable|numeric|min:0|max:100',
            'assessment_date'    => 'nullable|date',
            'submission_deadline'=> 'nullable|date',
            'description'        => 'nullable|string|max:1000',
            'status'             => 'required|in:' . implode(',', array_keys(Assessment::STATUSES)),
        ]);

        $assessment->update($validated);

        return redirect()->route('assessments.show', $assessment)->with('success', 'Assessment updated.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        abort_unless(auth()->user()->can('delete assessments'), 403);
        $this->authorizeSchool($assessment);

        if ($assessment->marks()->exists()) {
            return back()->with('error', 'Cannot delete assessment that has marks entered.');
        }

        $assessment->delete();

        return redirect()->route('assessments.index')->with('success', 'Assessment deleted.');
    }

    public function enterMarks(Request $request, Assessment $assessment): RedirectResponse
    {
        abort_unless(auth()->user()->can('enter marks'), 403);
        $this->authorizeSchool($assessment);

        $request->validate([
            'marks'           => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.score'   => 'nullable|numeric|min:0|max:' . $assessment->max_score,
            'marks.*.is_absent'  => 'boolean',
            'marks.*.is_exempt'  => 'boolean',
            'marks.*.remarks' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $assessment) {
            foreach ($request->input('marks') as $row) {
                $isAbsent = !empty($row['is_absent']);
                $isExempt = !empty($row['is_exempt']);

                Mark::updateOrCreate(
                    ['assessment_id' => $assessment->id, 'student_id' => $row['student_id']],
                    [
                        'score'      => ($isAbsent || $isExempt) ? null : ($row['score'] ?? null),
                        'is_absent'  => $isAbsent,
                        'is_exempt'  => $isExempt,
                        'remarks'    => $row['remarks'] ?? null,
                        'entered_by' => auth()->id(),
                        'entered_at' => now(),
                    ]
                );
            }

            $assessment->update(['marks_entered' => true, 'status' => 'completed']);
        });

        return redirect()->route('assessments.show', $assessment)->with('success', 'Marks saved successfully.');
    }

    private function authorizeSchool(Assessment $assessment): void
    {
        abort_unless($assessment->school_id == auth()->user()->school_id, 403);
    }

    private function teacherList(int $schoolId)
    {
        return User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'principal', 'vice-principal', 'school-admin']))
            ->orderBy('name')
            ->get();
    }
}
