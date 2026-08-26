<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrolment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnrolmentController extends Controller
{
    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function getAcademicYears()
    {
        return AcademicYear::where('school_id', $this->schoolId())
            ->orderByDesc('start_date')
            ->get();
    }

    private function getClasses()
    {
        return SchoolClass::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function authorizeSchoolAccess(Enrolment $enrolment): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) return;
        if ($enrolment->student?->school_id !== $user->school_id) {
            abort(403, 'Access denied.');
        }
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $schoolId     = $this->schoolId();
        $academicYears = $this->getAcademicYears();
        $currentYear  = $academicYears->firstWhere('is_current', true) ?? $academicYears->first();
        $selectedYearId = $request->year_id ?? $currentYear?->id;
        $classes      = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();

        // Base query (filters without status)
        $baseQuery = Enrolment::with(['student', 'schoolClass', 'term'])
            ->forSchool($schoolId)
            ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->search, fn($q) => $q->whereHas('student', fn($sq) =>
                $sq->where('first_name', 'like', "%{$request->search}%")
                   ->orWhere('last_name', 'like', "%{$request->search}%")
                   ->orWhere('student_number', 'like', "%{$request->search}%")
            ));

        // Status summary from the unfiltered-by-status query
        $statusCounts = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status');

        if ($request->status) {
            $baseQuery->where('status', $request->status);
        }

        $enrolments = $baseQuery
            ->orderBy('class_id')
            ->orderBy('roll_number')
            ->paginate(50)
            ->withQueryString();

        return view('enrolments.index', compact(
            'enrolments', 'academicYears', 'classes', 'selectedYearId', 'statusCounts'
        ));
    }

    // ---------------------------------------------------------------
    // CREATE / STORE (single)
    // ---------------------------------------------------------------

    public function create(Request $request)
    {
        $schoolId      = $this->schoolId();
        $academicYears = $this->getAcademicYears();
        $currentYear   = $academicYears->firstWhere('is_current', true) ?? $academicYears->first();
        $classes       = $this->getClasses();

        $preStudent = $request->student_id
            ? Student::where('school_id', $schoolId)->findOrFail($request->student_id)
            : null;
        $preClass   = $request->class_id
            ? SchoolClass::where('school_id', $schoolId)->findOrFail($request->class_id)
            : null;

        $terms = $currentYear
            ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get()
            : collect();

        return view('enrolments.create', compact(
            'academicYears', 'currentYear', 'classes', 'terms', 'preStudent', 'preClass'
        ));
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'student_id'       => ['required', 'integer',
                Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'class_id'         => ['required', 'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', 'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id'          => ['nullable', 'integer', 'exists:terms,id'],
            'roll_number'      => ['nullable', 'string', 'max:20'],
            'enrolled_date'    => ['required', 'date'],
            'status'           => ['required', Rule::in(array_keys(Enrolment::STATUSES))],
        ]);

        $exists = Enrolment::where('student_id', $validated['student_id'])
            ->where('class_id', $validated['class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'This student is already enrolled in that class for the selected academic year.');
        }

        Enrolment::create($validated);

        return redirect()->route('enrolments.index')
            ->with('success', 'Student enrolled successfully.');
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    public function show(Enrolment $enrolment)
    {
        $this->authorizeSchoolAccess($enrolment);
        $enrolment->load(['student', 'schoolClass', 'academicYear', 'term']);
        return view('enrolments.show', compact('enrolment'));
    }

    // ---------------------------------------------------------------
    // EDIT / UPDATE
    // ---------------------------------------------------------------

    public function edit(Enrolment $enrolment)
    {
        $this->authorizeSchoolAccess($enrolment);
        $enrolment->load(['student', 'schoolClass', 'academicYear', 'term']);
        $terms = Term::where('academic_year_id', $enrolment->academic_year_id)
            ->orderBy('sequence')->get();

        return view('enrolments.edit', compact('enrolment', 'terms'));
    }

    public function update(Request $request, Enrolment $enrolment)
    {
        $this->authorizeSchoolAccess($enrolment);

        $validated = $request->validate([
            'term_id'       => ['nullable', 'integer', 'exists:terms,id'],
            'roll_number'   => ['nullable', 'string', 'max:20'],
            'enrolled_date' => ['required', 'date'],
            'exit_date'     => ['nullable', 'date', 'after_or_equal:enrolled_date'],
            'exit_reason'   => ['nullable', 'string', 'max:500'],
            'is_promoted'   => ['nullable', 'boolean'],
            'status'        => ['required', Rule::in(array_keys(Enrolment::STATUSES))],
        ]);

        $validated['is_promoted'] = $request->boolean('is_promoted');

        $enrolment->update($validated);

        return redirect()->route('enrolments.show', $enrolment)
            ->with('success', 'Enrolment updated.');
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    public function destroy(Enrolment $enrolment)
    {
        $this->authorizeSchoolAccess($enrolment);
        $enrolment->delete();
        return redirect()->route('enrolments.index')
            ->with('success', 'Enrolment record removed.');
    }

    // ---------------------------------------------------------------
    // WITHDRAW
    // ---------------------------------------------------------------

    public function withdraw(Request $request, Enrolment $enrolment)
    {
        $this->authorizeSchoolAccess($enrolment);

        $validated = $request->validate([
            'exit_date'   => ['required', 'date'],
            'exit_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $enrolment->update([
            'status'      => 'withdrawn',
            'exit_date'   => $validated['exit_date'],
            'exit_reason' => $validated['exit_reason'],
        ]);

        return back()->with('success', 'Student withdrawn from this enrolment.');
    }

    // ---------------------------------------------------------------
    // BULK ENROL
    // ---------------------------------------------------------------

    public function bulk(Request $request)
    {
        $schoolId      = $this->schoolId();
        $academicYears = $this->getAcademicYears();
        $currentYear   = $academicYears->firstWhere('is_current', true) ?? $academicYears->first();
        $classes       = $this->getClasses();

        $terms = $currentYear
            ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get()
            : collect();

        $unenrolledStudents = collect();
        $selectedClass      = null;
        $selectedYear       = null;

        if ($request->class_id && $request->year_id) {
            $selectedClass = SchoolClass::where('school_id', $schoolId)
                ->findOrFail($request->class_id);
            $selectedYear = AcademicYear::where('school_id', $schoolId)
                ->findOrFail($request->year_id);

            $terms = Term::where('academic_year_id', $request->year_id)
                ->orderBy('sequence')->get();

            $enrolledIds = Enrolment::where('class_id', $request->class_id)
                ->where('academic_year_id', $request->year_id)
                ->pluck('student_id');

            $unenrolledStudents = Student::where('school_id', $schoolId)
                ->where('status', 'active')
                ->whereNotIn('id', $enrolledIds)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('enrolments.bulk', compact(
            'academicYears', 'currentYear', 'classes', 'terms',
            'unenrolledStudents', 'selectedClass', 'selectedYear'
        ));
    }

    public function bulkStore(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'class_id'         => ['required', 'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', 'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id'          => ['nullable', 'integer', 'exists:terms,id'],
            'enrolled_date'    => ['required', 'date'],
            'student_ids'      => ['required', 'array', 'min:1'],
            'student_ids.*'    => ['integer',
                Rule::exists('students', 'id')->where('school_id', $schoolId)],
        ]);

        $alreadyEnrolled = Enrolment::where('class_id', $validated['class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->whereIn('student_id', $validated['student_ids'])
            ->pluck('student_id')
            ->toArray();

        $toEnrol = array_diff($validated['student_ids'], $alreadyEnrolled);
        $created = 0;

        foreach ($toEnrol as $studentId) {
            Enrolment::create([
                'student_id'       => $studentId,
                'class_id'         => $validated['class_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'term_id'          => $validated['term_id'] ?? null,
                'enrolled_date'    => $validated['enrolled_date'],
                'status'           => 'active',
            ]);
            $created++;
        }

        $msg = "{$created} student(s) enrolled successfully.";
        if (count($alreadyEnrolled) > 0) {
            $msg .= ' ' . count($alreadyEnrolled) . ' were already enrolled and skipped.';
        }

        return redirect()->route('enrolments.index')->with('success', $msg);
    }

    // ---------------------------------------------------------------
    // PROMOTE (class carry-forward)
    // ---------------------------------------------------------------

    public function promote(Request $request)
    {
        $schoolId      = $this->schoolId();
        $academicYears = $this->getAcademicYears();
        $classes       = $this->getClasses();

        $previewEnrolments = collect();
        $sourceClass       = null;
        $sourceYear        = null;

        if ($request->source_class_id && $request->source_year_id) {
            $sourceClass = SchoolClass::where('school_id', $schoolId)
                ->findOrFail($request->source_class_id);
            $sourceYear = AcademicYear::where('school_id', $schoolId)
                ->findOrFail($request->source_year_id);

            $previewEnrolments = Enrolment::with('student')
                ->where('class_id', $request->source_class_id)
                ->where('academic_year_id', $request->source_year_id)
                ->where('status', 'active')
                ->orderBy('roll_number')
                ->get();
        }

        return view('enrolments.promote', compact(
            'academicYears', 'classes', 'previewEnrolments', 'sourceClass', 'sourceYear'
        ));
    }

    public function promoteStore(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'source_class_id' => ['required', 'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'source_year_id'  => ['required', 'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'target_class_id' => ['required', 'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'target_year_id'  => ['required', 'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'enrolled_date'   => ['required', 'date'],
            'student_ids'     => ['required', 'array', 'min:1'],
            'student_ids.*'   => ['integer'],
        ]);

        $sourceEnrolments = Enrolment::where('class_id', $validated['source_class_id'])
            ->where('academic_year_id', $validated['source_year_id'])
            ->whereIn('student_id', $validated['student_ids'])
            ->where('status', 'active')
            ->get();

        $alreadyInTarget = Enrolment::where('class_id', $validated['target_class_id'])
            ->where('academic_year_id', $validated['target_year_id'])
            ->whereIn('student_id', $validated['student_ids'])
            ->pluck('student_id')
            ->toArray();

        $created = 0;

        DB::transaction(function () use ($sourceEnrolments, $validated, $alreadyInTarget, &$created) {
            foreach ($sourceEnrolments as $src) {
                if (in_array($src->student_id, $alreadyInTarget)) {
                    continue;
                }

                $src->update(['is_promoted' => true]);

                Enrolment::create([
                    'student_id'       => $src->student_id,
                    'class_id'         => $validated['target_class_id'],
                    'academic_year_id' => $validated['target_year_id'],
                    'enrolled_date'    => $validated['enrolled_date'],
                    'status'           => 'active',
                    'is_promoted'      => false,
                ]);

                $created++;
            }
        });

        return redirect()->route('enrolments.index')
            ->with('success', "{$created} student(s) promoted to the new class/year successfully.");
    }
}
