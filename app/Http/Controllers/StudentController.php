<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view students')->only(['index', 'show']);
        $this->middleware('can:create students')->only(['create', 'store']);
        $this->middleware('can:edit students')->only(['edit', 'update']);
        $this->middleware('can:delete students')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $query = Student::where('school_id', $schoolId)
            ->with(['currentClass', 'school'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->class_id, fn($q, $classId) => $q->where('current_class_id', $classId))
            ->when($request->gender, fn($q, $gender) => $q->where('gender', $gender));

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['first_name', 'last_name', 'student_number', 'admission_date', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $students = $query->paginate(20)->withQueryString();

        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
            ->orderBy('level')->orderBy('name')->get();

        $stats = [
            'total' => Student::where('school_id', $schoolId)->count(),
            'active' => Student::where('school_id', $schoolId)->where('status', 'active')->count(),
            'male' => Student::where('school_id', $schoolId)->where('gender', 'male')->where('status', 'active')->count(),
            'female' => Student::where('school_id', $schoolId)->where('gender', 'female')->where('status', 'active')->count(),
        ];

        return view('students.index', compact('students', 'classes', 'stats'));
    }

    public function create(): View
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
            ->orderBy('level')->orderBy('name')->get();
        return view('students.create', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'other_names' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'genotype' => ['nullable', 'string', 'max:10'],
            'house' => ['nullable', 'string', 'max:100'],
            'previous_school' => ['nullable', 'string', 'max:200'],
            'admission_date' => ['nullable', 'date'],
            'current_class_id' => ['nullable', 'exists:school_classes,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Generate student number
        $validated['student_number'] = $this->generateStudentNumber($schoolId);
        $validated['school_id'] = $schoolId;

        $student = Student::create($validated);

        return redirect()->route('students.show', $student)
            ->with('success', "Student {$student->full_name} created successfully.");
    }

    public function show(Student $student): View
    {
        $this->authorizeSchoolAccess($student);
        $student->load(['currentClass', 'guardians', 'enrolments.schoolClass', 'invoices', 'attendance']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->authorizeSchoolAccess($student);
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
            ->orderBy('level')->orderBy('name')->get();
        return view('students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeSchoolAccess($student);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'other_names' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'genotype' => ['nullable', 'string', 'max:10'],
            'house' => ['nullable', 'string', 'max:100'],
            'previous_school' => ['nullable', 'string', 'max:200'],
            'admission_date' => ['nullable', 'date'],
            'current_class_id' => ['nullable', 'exists:school_classes,id'],
            'status' => ['required', 'in:active,inactive,graduated,transferred,withdrawn,suspended'],
        ]);

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student record updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeSchoolAccess($student);
        $name = $student->full_name;
        $student->delete();
        return redirect()->route('students.index')
            ->with('success', "Student {$name} has been removed.");
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->user()->can('view students'), 403);

        $schoolId = auth()->user()->school_id;
        $q = '%' . $request->get('q', '') . '%';

        $students = Student::where('school_id', $schoolId)
            ->where(fn($query) => $query
                ->where('first_name', 'like', $q)
                ->orWhere('last_name', 'like', $q)
                ->orWhere('admission_number', 'like', $q))
            ->limit(15)
            ->get(['id', 'first_name', 'last_name', 'admission_number'])
            ->map(fn($s) => [
                'id'               => $s->id,
                'full_name'        => $s->full_name,
                'admission_number' => $s->admission_number,
            ]);

        return response()->json($students);
    }

    private function generateStudentNumber(int $schoolId): string
    {
        $setting = \App\Models\SystemSetting::where('school_id', $schoolId)
            ->where('key', 'student_number_prefix')->first();
        $prefix = $setting?->value ?? 'STU';
        $year = date('Y');

        $lastNumber = Student::where('school_id', $schoolId)
            ->where('student_number', 'like', "{$prefix}{$year}%")
            ->max('student_number');

        $seq = $lastNumber ? (int) substr($lastNumber, -4) + 1 : 1;

        return $prefix . $year . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function authorizeSchoolAccess(Student $student): void
    {
        if ($student->school_id != auth()->user()->school_id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }
    }
}
