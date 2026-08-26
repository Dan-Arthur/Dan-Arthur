<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view admissions')->only(['index', 'show']);
        $this->middleware('can:create admissions')->only(['create', 'store']);
        $this->middleware('can:edit admissions')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('can:delete admissions')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $query = Admission::where('school_id', $schoolId)
            ->with(['academicYear', 'appliedClass', 'campus'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('application_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->academic_year_id, fn($q, $y) => $q->where('academic_year_id', $y))
            ->when($request->class_id, fn($q, $c) => $q->where('applied_class_id', $c))
            ->when($request->gender, fn($q, $g) => $q->where('gender', $g));

        $sortField = $request->get('sort', 'created_at');
        $sortDir   = $request->get('dir', 'desc');
        $allowedSorts = ['first_name', 'last_name', 'application_number', 'application_date', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $admissions = $query->paginate(20)->withQueryString();

        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $classes       = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
                            ->orderBy('level')->orderBy('name')->get();
        $campuses      = Campus::where('school_id', $schoolId)->get();

        $stats = [
            'total'        => Admission::where('school_id', $schoolId)->count(),
            'submitted'    => Admission::where('school_id', $schoolId)->where('status', 'submitted')->count(),
            'under_review' => Admission::where('school_id', $schoolId)->where('status', 'under_review')->count(),
            'accepted'     => Admission::where('school_id', $schoolId)->where('status', 'accepted')->count(),
            'enrolled'     => Admission::where('school_id', $schoolId)->where('status', 'enrolled')->count(),
            'rejected'     => Admission::where('school_id', $schoolId)->where('status', 'rejected')->count(),
        ];

        $statusOptions = Admission::STATUSES;

        return view('admissions.index', compact(
            'admissions', 'stats', 'statusOptions', 'academicYears', 'classes', 'campuses'
        ));
    }

    public function create(): View
    {
        $schoolId    = auth()->user()->school_id;
        $classes     = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
                        ->orderBy('level')->orderBy('name')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $campuses    = Campus::where('school_id', $schoolId)->get();
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();

        return view('admissions.create', compact('classes', 'academicYears', 'campuses', 'currentYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'other_names'        => ['nullable', 'string', 'max:100'],
            'gender'             => ['required', 'in:male,female'],
            'date_of_birth'      => ['nullable', 'date', 'before:today'],
            'nationality'        => ['nullable', 'string', 'max:100'],
            'religion'           => ['nullable', 'string', 'max:50'],
            'address'            => ['nullable', 'string'],
            'email'              => ['nullable', 'email', 'max:150'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'previous_school'    => ['nullable', 'string', 'max:200'],
            'applying_for_class' => ['nullable', 'string', 'max:100'],
            'applied_class_id'   => ['nullable', 'exists:school_classes,id'],
            'academic_year_id'   => ['required', 'exists:academic_years,id'],
            'campus_id'          => ['nullable', 'exists:campuses,id'],
            'application_date'   => ['nullable', 'date'],
            'status'             => ['required', 'in:draft,submitted'],
            'notes'              => ['nullable', 'string'],
            // Guardian info
            'guardian_name'      => ['nullable', 'string', 'max:200'],
            'guardian_phone'     => ['nullable', 'string', 'max:30'],
            'guardian_email'     => ['nullable', 'email', 'max:150'],
            'guardian_relation'  => ['nullable', 'string', 'max:50'],
            'guardian_address'   => ['nullable', 'string'],
        ]);

        $validated['school_id']         = $schoolId;
        $validated['application_number'] = $this->generateApplicationNumber($schoolId);
        $validated['application_date']   = $validated['application_date'] ?? now()->toDateString();

        // Pack guardian info into JSON
        if ($request->filled('guardian_name')) {
            $validated['guardian_info'] = [
                'name'     => $request->guardian_name,
                'phone'    => $request->guardian_phone,
                'email'    => $request->guardian_email,
                'relation' => $request->guardian_relation,
                'address'  => $request->guardian_address,
            ];
        }
        unset($validated['guardian_name'], $validated['guardian_phone'],
              $validated['guardian_email'], $validated['guardian_relation'],
              $validated['guardian_address']);

        $admission = Admission::create($validated);

        return redirect()->route('admissions.show', $admission)
            ->with('success', "Application #{$admission->application_number} for {$admission->full_name} created.");
    }

    public function show(Admission $admission): View
    {
        $this->authorizeSchoolAccess($admission);
        $admission->load(['academicYear', 'appliedClass', 'campus', 'reviewer', 'decisionMaker', 'student']);
        $statusOptions = Admission::STATUSES;
        return view('admissions.show', compact('admission', 'statusOptions'));
    }

    public function edit(Admission $admission): View
    {
        $this->authorizeSchoolAccess($admission);
        $schoolId    = auth()->user()->school_id;
        $classes     = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
                        ->orderBy('level')->orderBy('name')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $campuses    = Campus::where('school_id', $schoolId)->get();

        return view('admissions.edit', compact('admission', 'classes', 'academicYears', 'campuses'));
    }

    public function update(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorizeSchoolAccess($admission);

        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'other_names'        => ['nullable', 'string', 'max:100'],
            'gender'             => ['required', 'in:male,female'],
            'date_of_birth'      => ['nullable', 'date', 'before:today'],
            'nationality'        => ['nullable', 'string', 'max:100'],
            'religion'           => ['nullable', 'string', 'max:50'],
            'address'            => ['nullable', 'string'],
            'email'              => ['nullable', 'email', 'max:150'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'previous_school'    => ['nullable', 'string', 'max:200'],
            'applying_for_class' => ['nullable', 'string', 'max:100'],
            'applied_class_id'   => ['nullable', 'exists:school_classes,id'],
            'academic_year_id'   => ['required', 'exists:academic_years,id'],
            'campus_id'          => ['nullable', 'exists:campuses,id'],
            'application_date'   => ['nullable', 'date'],
            'interview_date'     => ['nullable', 'date'],
            'exam_date'          => ['nullable', 'date'],
            'notes'              => ['nullable', 'string'],
            'guardian_name'      => ['nullable', 'string', 'max:200'],
            'guardian_phone'     => ['nullable', 'string', 'max:30'],
            'guardian_email'     => ['nullable', 'email', 'max:150'],
            'guardian_relation'  => ['nullable', 'string', 'max:50'],
            'guardian_address'   => ['nullable', 'string'],
        ]);

        if ($request->filled('guardian_name')) {
            $validated['guardian_info'] = [
                'name'     => $request->guardian_name,
                'phone'    => $request->guardian_phone,
                'email'    => $request->guardian_email,
                'relation' => $request->guardian_relation,
                'address'  => $request->guardian_address,
            ];
        }
        unset($validated['guardian_name'], $validated['guardian_phone'],
              $validated['guardian_email'], $validated['guardian_relation'],
              $validated['guardian_address']);

        $admission->update($validated);

        return redirect()->route('admissions.show', $admission)
            ->with('success', 'Application updated successfully.');
    }

    public function updateStatus(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorizeSchoolAccess($admission);

        $validated = $request->validate([
            'status'         => ['required', 'in:' . implode(',', array_keys(Admission::STATUSES))],
            'decision_notes' => ['nullable', 'string'],
            'interview_date' => ['nullable', 'date'],
            'exam_date'      => ['nullable', 'date'],
        ]);

        $newStatus = $validated['status'];

        if (!$admission->canAdvanceTo($newStatus)) {
            return back()->with('error', "Cannot move application from '{$admission->status_label}' to '{$newStatus}'.");
        }

        $updates = ['status' => $newStatus];

        if (in_array($newStatus, ['accepted', 'rejected', 'waitlisted', 'enrolled'])) {
            $updates['decision_date'] = now()->toDateString();
            $updates['decided_by']   = auth()->id();
        }

        if ($newStatus === 'under_review') {
            $updates['reviewed_by'] = auth()->id();
        }

        if (!empty($validated['decision_notes'])) {
            $updates['decision_notes'] = $validated['decision_notes'];
        }

        if (!empty($validated['interview_date'])) {
            $updates['interview_date'] = $validated['interview_date'];
        }

        if (!empty($validated['exam_date'])) {
            $updates['exam_date'] = $validated['exam_date'];
        }

        $admission->update($updates);

        return redirect()->route('admissions.show', $admission)
            ->with('success', "Application status updated to: {$admission->fresh()->status_label}");
    }

    public function enrol(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorizeSchoolAccess($admission);

        if ($admission->status !== 'accepted') {
            return back()->with('error', 'Only accepted applications can be enrolled as students.');
        }

        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'current_class_id' => ['required', 'exists:school_classes,id'],
            'admission_date'   => ['required', 'date'],
        ]);

        $guardian = $admission->guardian_info ?? [];

        $student = Student::create([
            'school_id'               => $schoolId,
            'campus_id'               => $admission->campus_id,
            'first_name'              => $admission->first_name,
            'last_name'               => $admission->last_name,
            'other_names'             => $admission->other_names,
            'gender'                  => $admission->gender,
            'date_of_birth'           => $admission->date_of_birth,
            'nationality'             => $admission->nationality,
            'religion'                => $admission->religion,
            'address'                 => $admission->address,
            'email'                   => $admission->email,
            'phone'                   => $admission->phone,
            'previous_school'         => $admission->previous_school,
            'admission_number'        => $admission->application_number,
            'admission_date'          => $validated['admission_date'],
            'current_class_id'        => $validated['current_class_id'],
            'status'                  => 'active',
            'student_number'          => $this->generateStudentNumber($schoolId),
        ]);

        $admission->update([
            'status'     => 'enrolled',
            'student_id' => $student->id,
            'decided_by' => auth()->id(),
            'decision_date' => now()->toDateString(),
        ]);

        return redirect()->route('students.show', $student)
            ->with('success', "Student {$student->full_name} has been enrolled successfully.");
    }

    public function destroy(Admission $admission): RedirectResponse
    {
        $this->authorizeSchoolAccess($admission);
        $ref = $admission->application_number;
        $admission->delete();
        return redirect()->route('admissions.index')
            ->with('success', "Application #{$ref} has been deleted.");
    }

    private function generateApplicationNumber(int $schoolId): string
    {
        $setting = SystemSetting::where('school_id', $schoolId)
            ->where('key', 'admission_number_prefix')->first();
        $prefix = $setting?->value ?? 'APP';
        $year   = date('Y');

        $last = Admission::where('school_id', $schoolId)
            ->where('application_number', 'like', "{$prefix}{$year}%")
            ->max('application_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . $year . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function generateStudentNumber(int $schoolId): string
    {
        $setting = SystemSetting::where('school_id', $schoolId)
            ->where('key', 'student_number_prefix')->first();
        $prefix = $setting?->value ?? 'STU';
        $year   = date('Y');

        $last = Student::where('school_id', $schoolId)
            ->where('student_number', 'like', "{$prefix}{$year}%")
            ->max('student_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . $year . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function authorizeSchoolAccess(Admission $admission): void
    {
        if ($admission->school_id != auth()->user()->school_id && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Access denied.');
        }
    }
}
