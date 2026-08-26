<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeSubject(Subject $subject): void
    {
        abort_unless($subject->school_id == $this->schoolId(), 403);
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();

        $departments = Department::where('school_id', $schoolId)
            ->where('is_active', true)->orderBy('name')->get();

        $query = Subject::with('department')
            ->forSchool($schoolId)
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($request->status !== 'inactive', fn($q) => $q->where('is_active', true))
            ->orderBy('name');

        $subjects = $query->paginate(40)->withQueryString();

        $typeCounts = Subject::forSchool($schoolId)
            ->select('type', \DB::raw('COUNT(*) as cnt'))
            ->groupBy('type')->pluck('cnt', 'type');

        return view('subjects.index', compact('subjects', 'departments', 'typeCounts'));
    }

    // ---------------------------------------------------------------
    // CREATE / STORE
    // ---------------------------------------------------------------

    public function create()
    {
        $schoolId = $this->schoolId();
        $departments = Department::where('school_id', $schoolId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('subjects.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'code'          => ['required', 'string', 'max:30',
                Rule::unique('subjects')->where('school_id', $schoolId)],
            'type'          => ['required', Rule::in(array_keys(Subject::TYPES))],
            'category'      => ['nullable', Rule::in(array_keys(Subject::CATEGORIES))],
            'department_id' => ['nullable', 'integer',
                Rule::exists('departments', 'id')->where('school_id', $schoolId)],
            'credit_hours'  => ['required', 'integer', 'min:1', 'max:10'],
            'has_practical' => ['nullable', 'boolean'],
        ]);

        $validated['school_id']    = $schoolId;
        $validated['has_practical']= $request->boolean('has_practical');
        $validated['is_active']    = true;

        Subject::create($validated);

        return redirect()->route('subjects.index')
            ->with('success', "Subject '{$validated['name']}' created.");
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    public function show(Subject $subject)
    {
        $this->authorizeSubject($subject);
        $schoolId = $this->schoolId();

        $subject->load('department');

        $assignments = ClassSubject::with(['schoolClass', 'academicYear', 'teacher'])
            ->where('subject_id', $subject->id)
            ->orderByDesc('academic_year_id')
            ->orderBy('class_id')
            ->get()
            ->groupBy('academic_year_id');

        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')->get();
        $currentYear = $academicYears->firstWhere('is_current', true) ?? $academicYears->first();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)->orderBy('name')->get();

        $teachers = User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) =>
                $q->whereIn('name', ['teacher', 'principal', 'vice-principal', 'school-admin']))
            ->orderBy('last_name')->orderBy('first_name')->get();

        // Classes already assigned in the current year (to exclude from dropdown)
        $assignedClassIds = ClassSubject::where('subject_id', $subject->id)
            ->where('academic_year_id', $currentYear?->id)
            ->pluck('class_id');

        return view('subjects.show', compact(
            'subject', 'assignments', 'academicYears', 'currentYear',
            'classes', 'teachers', 'assignedClassIds'
        ));
    }

    // ---------------------------------------------------------------
    // EDIT / UPDATE
    // ---------------------------------------------------------------

    public function edit(Subject $subject)
    {
        $this->authorizeSubject($subject);
        $schoolId = $this->schoolId();

        $departments = Department::where('school_id', $schoolId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('subjects.edit', compact('subject', 'departments'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeSubject($subject);
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'code'          => ['required', 'string', 'max:30',
                Rule::unique('subjects')->where('school_id', $schoolId)->ignore($subject->id)],
            'type'          => ['required', Rule::in(array_keys(Subject::TYPES))],
            'category'      => ['nullable', Rule::in(array_keys(Subject::CATEGORIES))],
            'department_id' => ['nullable', 'integer',
                Rule::exists('departments', 'id')->where('school_id', $schoolId)],
            'credit_hours'  => ['required', 'integer', 'min:1', 'max:10'],
            'has_practical' => ['nullable', 'boolean'],
        ]);

        $validated['has_practical'] = $request->boolean('has_practical');
        $subject->update($validated);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Subject updated.');
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    public function destroy(Subject $subject)
    {
        $this->authorizeSubject($subject);

        if ($subject->classSubjects()->exists()) {
            return back()->with('error',
                'Cannot delete a subject that is assigned to classes. Unassign it first.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted.');
    }

    // ---------------------------------------------------------------
    // TOGGLE ACTIVE
    // ---------------------------------------------------------------

    public function toggleActive(Subject $subject)
    {
        $this->authorizeSubject($subject);
        $subject->update(['is_active' => !$subject->is_active]);

        return back()->with('success',
            "'{$subject->name}' " . ($subject->is_active ? 'activated' : 'deactivated') . '.');
    }

    // ---------------------------------------------------------------
    // CLASS ASSIGNMENTS
    // ---------------------------------------------------------------

    public function assignClass(Request $request, Subject $subject)
    {
        $this->authorizeSubject($subject);
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'class_id'         => ['required', 'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', 'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'teacher_id'       => ['nullable', 'integer',
                Rule::exists('users', 'id')->where('school_id', $schoolId)],
            'is_compulsory'    => ['nullable', 'boolean'],
            'periods_per_week' => ['required', 'integer', 'min:1', 'max:40'],
        ]);

        $exists = ClassSubject::where('class_id', $validated['class_id'])
            ->where('subject_id', $subject->id)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This subject is already assigned to that class for the selected year.');
        }

        ClassSubject::create(array_merge($validated, [
            'subject_id'    => $subject->id,
            'is_compulsory' => $request->boolean('is_compulsory'),
        ]));

        return back()->with('success', 'Subject assigned to class.');
    }

    public function updateAssignment(Request $request, Subject $subject, ClassSubject $assignment)
    {
        $this->authorizeSubject($subject);
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'teacher_id'       => ['nullable', 'integer',
                Rule::exists('users', 'id')->where('school_id', $schoolId)],
            'is_compulsory'    => ['nullable', 'boolean'],
            'periods_per_week' => ['required', 'integer', 'min:1', 'max:40'],
        ]);

        $validated['is_compulsory'] = $request->boolean('is_compulsory');
        $assignment->update($validated);

        return back()->with('success', 'Assignment updated.');
    }

    public function unassignClass(Subject $subject, ClassSubject $assignment)
    {
        $this->authorizeSubject($subject);
        $assignment->delete();

        return back()->with('success', 'Subject unassigned from class.');
    }
}
