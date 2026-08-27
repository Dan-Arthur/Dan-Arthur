<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\DisciplinaryRecord;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisciplinaryRecordController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view disciplinary records'), 403);

        $schoolId = $this->schoolId();

        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();

        $query = DisciplinaryRecord::where('school_id', $schoolId)
            ->with(['student', 'reportedBy', 'academicYear', 'term']);

        if ($request->filled('year_id')) {
            $query->where('academic_year_id', $request->integer('year_id'));
        } elseif ($currentYear) {
            $query->where('academic_year_id', $currentYear->id);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->whereHas('student', fn($sq) =>
                $sq->where('first_name', 'like', $q)
                   ->orWhere('last_name', 'like', $q)
                   ->orWhere('student_number', 'like', $q)
            );
        }

        $records = $query->orderByDesc('incident_date')->paginate(25)->withQueryString();

        $terms = $currentYear
            ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get()
            : collect();

        return view('disciplinary.index', compact('records', 'years', 'currentYear', 'terms'));
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->can('create disciplinary records'), 403);

        $schoolId = $this->schoolId();

        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $terms       = $currentYear
            ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get()
            : collect();

        $student = null;
        if ($request->filled('student_id')) {
            $student = Student::where('school_id', $schoolId)->find($request->integer('student_id'));
        }

        return view('disciplinary.create', compact('years', 'currentYear', 'terms', 'student'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('create disciplinary records'), 403);

        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'nullable|exists:terms,id',
            'category'         => 'required|string|max:100',
            'severity'         => 'required|in:minor,moderate,major',
            'incident_date'    => 'required|date',
            'location'         => 'nullable|string|max:200',
            'description'      => 'required|string|max:3000',
            'action_taken'     => 'nullable|string|max:2000',
            'follow_up_date'   => 'nullable|date|after_or_equal:incident_date',
            'follow_up_notes'  => 'nullable|string|max:2000',
            'parent_notified'  => 'boolean',
            'status'           => 'required|in:open,pending_review,resolved',
        ]);

        $schoolId = $this->schoolId();

        abort_unless(
            Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->exists(),
            403
        );

        $record = DisciplinaryRecord::create(array_merge($validated, [
            'school_id'   => $schoolId,
            'reported_by' => auth()->id(),
            'parent_notified'    => $request->boolean('parent_notified'),
            'parent_notified_at' => $request->boolean('parent_notified') ? now() : null,
        ]));

        return redirect()->route('disciplinary.show', $record)
            ->with('success', 'Disciplinary record created.');
    }

    public function show(DisciplinaryRecord $disciplinary): View
    {
        abort_unless(auth()->user()->can('view disciplinary records'), 403);
        abort_unless($disciplinary->school_id == $this->schoolId(), 403);

        $disciplinary->load(['student.classroom', 'reportedBy', 'academicYear', 'term']);

        return view('disciplinary.show', compact('disciplinary'));
    }

    public function edit(DisciplinaryRecord $disciplinary): View
    {
        abort_unless(auth()->user()->can('edit disciplinary records'), 403);
        abort_unless($disciplinary->school_id == $this->schoolId(), 403);

        $schoolId = $this->schoolId();

        $years = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $terms = Term::where('academic_year_id', $disciplinary->academic_year_id)->orderBy('sequence')->get();

        $disciplinary->load(['student', 'academicYear', 'term']);

        return view('disciplinary.edit', compact('disciplinary', 'years', 'terms'));
    }

    public function update(Request $request, DisciplinaryRecord $disciplinary): RedirectResponse
    {
        abort_unless(auth()->user()->can('edit disciplinary records'), 403);
        abort_unless($disciplinary->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'nullable|exists:terms,id',
            'category'         => 'required|string|max:100',
            'severity'         => 'required|in:minor,moderate,major',
            'incident_date'    => 'required|date',
            'location'         => 'nullable|string|max:200',
            'description'      => 'required|string|max:3000',
            'action_taken'     => 'nullable|string|max:2000',
            'follow_up_date'   => 'nullable|date|after_or_equal:incident_date',
            'follow_up_notes'  => 'nullable|string|max:2000',
            'parent_notified'  => 'boolean',
            'status'           => 'required|in:open,pending_review,resolved',
        ]);

        $wasNotified = $disciplinary->parent_notified;
        $nowNotified = $request->boolean('parent_notified');

        $disciplinary->update(array_merge($validated, [
            'parent_notified'    => $nowNotified,
            'parent_notified_at' => ($nowNotified && !$wasNotified) ? now() : $disciplinary->parent_notified_at,
        ]));

        return redirect()->route('disciplinary.show', $disciplinary)
            ->with('success', 'Record updated.');
    }

    public function destroy(DisciplinaryRecord $disciplinary): RedirectResponse
    {
        abort_unless(auth()->user()->can('delete disciplinary records'), 403);
        abort_unless($disciplinary->school_id == $this->schoolId(), 403);

        $disciplinary->delete();

        return redirect()->route('disciplinary.index')
            ->with('success', 'Record deleted.');
    }

    public function searchStudents(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->user()->can('create disciplinary records'), 403);

        $q        = $request->get('q', '');
        $schoolId = $this->schoolId();

        $students = Student::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('student_number', 'like', "%{$q}%")
                      ->orWhere('admission_number', 'like', "%{$q}%");
            })
            ->select('id', 'first_name', 'last_name', 'student_number', 'admission_number')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'label' => $s->full_name . ' (' . ($s->student_number ?: $s->admission_number) . ')',
            ]);

        return response()->json($students);
    }

    public function termsByYear(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->user()->can('view disciplinary records'), 403);

        $terms = Term::where('academic_year_id', $request->integer('year_id'))
            ->orderBy('sequence')
            ->get(['id', 'name']);

        return response()->json($terms);
    }

    public function studentHistory(Student $student): View
    {
        abort_unless(auth()->user()->can('view disciplinary records'), 403);
        abort_unless($student->school_id == $this->schoolId(), 403);

        $records = DisciplinaryRecord::where('student_id', $student->id)
            ->where('school_id', $this->schoolId())
            ->with(['reportedBy', 'academicYear', 'term'])
            ->orderByDesc('incident_date')
            ->get();

        return view('disciplinary.student-history', compact('student', 'records'));
    }
}
