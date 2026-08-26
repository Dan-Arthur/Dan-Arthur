<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view classes')->only(['index', 'show']);
        $this->middleware('can:create classes')->only(['create', 'store']);
        $this->middleware('can:edit classes')->only(['edit', 'update', 'toggleActive']);
        $this->middleware('can:delete classes')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $query = SchoolClass::where('school_id', $schoolId)
            ->with(['classTeacher', 'campus', 'department'])
            ->withCount('students')
            ->when($request->search, fn($q, $s) =>
                $q->where(function ($inner) use ($s) {
                    $inner->where('name', 'like', "%{$s}%")
                          ->orWhere('code', 'like', "%{$s}%")
                          ->orWhere('section', 'like', "%{$s}%");
                })
            )
            ->when($request->programme, fn($q, $p) => $q->where('programme', $p))
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->when($request->campus_id, fn($q, $c) => $q->where('campus_id', $c))
            ->orderBy('level')
            ->orderBy('name')
            ->orderBy('section');

        $classes = $query->paginate(25)->withQueryString();

        $programmes = SchoolClass::where('school_id', $schoolId)
            ->whereNotNull('programme')
            ->distinct()->pluck('programme');

        $campuses = Campus::where('school_id', $schoolId)->get();

        $stats = [
            'total'    => SchoolClass::where('school_id', $schoolId)->count(),
            'active'   => SchoolClass::where('school_id', $schoolId)->where('is_active', true)->count(),
            'enrolled' => Student::where('school_id', $schoolId)->where('status', 'active')
                            ->whereNotNull('current_class_id')->count(),
        ];

        return view('classes.index', compact('classes', 'stats', 'programmes', 'campuses'));
    }

    public function create(): View
    {
        $schoolId   = auth()->user()->school_id;
        $campuses   = Campus::where('school_id', $schoolId)->get();
        $departments = Department::where('school_id', $schoolId)->where('is_active', true)->get();
        $teachers   = $this->getTeachers($schoolId);

        return view('classes.create', compact('campuses', 'departments', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'code'             => ['required', 'string', 'max:20'],
            'level'            => ['required', 'integer', 'min:1', 'max:20'],
            'section'          => ['nullable', 'string', 'max:50'],
            'programme'        => ['nullable', 'string', 'max:100'],
            'capacity'         => ['required', 'integer', 'min:1', 'max:200'],
            'room'             => ['nullable', 'string', 'max:100'],
            'campus_id'        => ['nullable', 'exists:campuses,id'],
            'department_id'    => ['nullable', 'exists:departments,id'],
            'class_teacher_id' => ['nullable', 'exists:users,id'],
            'is_active'        => ['boolean'],
        ]);

        // Validate unique code + section per school
        $exists = SchoolClass::where('school_id', $schoolId)
            ->where('code', $validated['code'])
            ->where('section', $validated['section'] ?? null)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'A class with this code and section already exists.']);
        }

        $validated['school_id'] = $schoolId;
        $validated['is_active'] = $request->boolean('is_active', true);

        $class = SchoolClass::create($validated);

        return redirect()->route('classes.show', $class)
            ->with('success', "Class {$class->full_name} created successfully.");
    }

    public function show(SchoolClass $class): View
    {
        $this->authorizeSchoolAccess($class);

        $class->load(['classTeacher', 'campus', 'department']);

        $students = Student::where('current_class_id', $class->id)
            ->where('school_id', $class->school_id)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'enrolled'  => $students->count(),
            'capacity'  => $class->capacity,
            'available' => max(0, $class->capacity - $students->count()),
            'occupancy' => $class->capacity > 0
                ? min(100, (int) round(($students->count() / $class->capacity) * 100))
                : 0,
            'male'      => $students->where('gender', 'male')->count(),
            'female'    => $students->where('gender', 'female')->count(),
        ];

        return view('classes.show', compact('class', 'students', 'stats'));
    }

    public function edit(SchoolClass $class): View
    {
        $this->authorizeSchoolAccess($class);

        $schoolId    = auth()->user()->school_id;
        $campuses    = Campus::where('school_id', $schoolId)->get();
        $departments = Department::where('school_id', $schoolId)->where('is_active', true)->get();
        $teachers    = $this->getTeachers($schoolId);

        return view('classes.edit', compact('class', 'campuses', 'departments', 'teachers'));
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $this->authorizeSchoolAccess($class);

        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'code'             => ['required', 'string', 'max:20'],
            'level'            => ['required', 'integer', 'min:1', 'max:20'],
            'section'          => ['nullable', 'string', 'max:50'],
            'programme'        => ['nullable', 'string', 'max:100'],
            'capacity'         => ['required', 'integer', 'min:1', 'max:200'],
            'room'             => ['nullable', 'string', 'max:100'],
            'campus_id'        => ['nullable', 'exists:campuses,id'],
            'department_id'    => ['nullable', 'exists:departments,id'],
            'class_teacher_id' => ['nullable', 'exists:users,id'],
            'is_active'        => ['boolean'],
        ]);

        // Unique code + section per school, excluding self
        $exists = SchoolClass::where('school_id', $schoolId)
            ->where('code', $validated['code'])
            ->where('section', $validated['section'] ?? null)
            ->where('id', '!=', $class->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'Another class with this code and section already exists.']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $class->update($validated);

        return redirect()->route('classes.show', $class)
            ->with('success', 'Class updated successfully.');
    }

    public function toggleActive(SchoolClass $class): RedirectResponse
    {
        $this->authorizeSchoolAccess($class);
        $class->update(['is_active' => !$class->is_active]);
        $state = $class->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Class {$class->full_name} {$state}.");
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $this->authorizeSchoolAccess($class);

        if ($class->students()->count() > 0) {
            return back()->with('error', 'Cannot delete a class with enrolled students. Remove students first.');
        }

        $name = $class->full_name;
        $class->delete();
        return redirect()->route('classes.index')
            ->with('success', "Class {$name} deleted.");
    }

    private function getTeachers(int $schoolId): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereHas('roles', fn($q) =>
                $q->whereIn('name', ['teacher', 'principal', 'vice-principal', 'school-admin'])
            )
            ->orderBy('name')
            ->get();
    }

    private function authorizeSchoolAccess(SchoolClass $class): void
    {
        if ($class->school_id != auth()->user()->school_id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }
    }
}
