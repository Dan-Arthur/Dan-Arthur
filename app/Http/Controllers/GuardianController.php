<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view guardians')->only(['index', 'show']);
        $this->middleware('can:create guardians')->only(['create', 'store']);
        $this->middleware('can:edit guardians')->only(['edit', 'update', 'togglePortal']);
        $this->middleware('can:delete guardians')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $query = Guardian::where('school_id', $schoolId)
            ->with(['students'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->portal, fn($q) => $q->where('portal_access', $request->portal === 'yes'));

        $sortField = $request->get('sort', 'created_at');
        $sortDir   = $request->get('dir', 'desc');
        $allowedSorts = ['first_name', 'last_name', 'phone', 'email', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $guardians = $query->paginate(20)->withQueryString();

        $stats = [
            'total'         => Guardian::where('school_id', $schoolId)->count(),
            'portal_access' => Guardian::where('school_id', $schoolId)->where('portal_access', true)->count(),
            'active'        => Guardian::where('school_id', $schoolId)->where('status', 'active')->count(),
        ];

        return view('guardians.index', compact('guardians', 'stats'));
    }

    public function create(Request $request): View
    {
        $schoolId   = auth()->user()->school_id;
        $students   = Student::where('school_id', $schoolId)->where('status', 'active')
                        ->orderBy('first_name')->orderBy('last_name')->get();
        $linkStudent = $request->student_id
            ? Student::where('school_id', $schoolId)->find($request->student_id)
            : null;

        return view('guardians.create', compact('students', 'linkStudent'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'title'                => ['nullable', 'string', 'max:20'],
            'first_name'           => ['required', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'other_names'          => ['nullable', 'string', 'max:100'],
            'gender'               => ['nullable', 'in:male,female'],
            'relationship'         => ['nullable', 'string', 'max:50'],
            'phone'                => ['required', 'string', 'max:30'],
            'alt_phone'            => ['nullable', 'string', 'max:30'],
            'email'                => ['nullable', 'email', 'max:150'],
            'occupation'           => ['nullable', 'string', 'max:150'],
            'employer'             => ['nullable', 'string', 'max:200'],
            'address'              => ['nullable', 'string'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'state'                => ['nullable', 'string', 'max:100'],
            'nationality'          => ['nullable', 'string', 'max:100'],
            'national_id'          => ['nullable', 'string', 'max:50'],
            'is_primary_contact'   => ['boolean'],
            'is_emergency_contact' => ['boolean'],
            'status'               => ['required', 'in:active,inactive'],
            // Pivot fields when linking a student immediately
            'link_student_id'      => ['nullable', 'exists:students,id'],
            'pivot_relationship'   => ['nullable', 'string', 'max:50'],
            'pivot_is_primary'     => ['boolean'],
            'pivot_is_emergency'   => ['boolean'],
            'pivot_can_pickup'     => ['boolean'],
            'pivot_receives_reports'  => ['boolean'],
            'pivot_receives_invoices' => ['boolean'],
        ]);

        $validated['school_id'] = $schoolId;
        $linkStudentId = $validated['link_student_id'] ?? null;
        $pivotData = [
            'relationship'     => $validated['pivot_relationship'] ?? $validated['relationship'] ?? null,
            'is_primary'       => (bool) ($validated['pivot_is_primary'] ?? false),
            'is_emergency'     => (bool) ($validated['pivot_is_emergency'] ?? false),
            'can_pickup'       => (bool) ($validated['pivot_can_pickup'] ?? true),
            'receives_reports' => (bool) ($validated['pivot_receives_reports'] ?? true),
            'receives_invoices'=> (bool) ($validated['pivot_receives_invoices'] ?? false),
        ];

        // Remove pivot fields from guardian data
        foreach (['link_student_id','pivot_relationship','pivot_is_primary','pivot_is_emergency',
                  'pivot_can_pickup','pivot_receives_reports','pivot_receives_invoices'] as $key) {
            unset($validated[$key]);
        }

        $guardian = Guardian::create($validated);

        if ($linkStudentId) {
            $student = Student::where('school_id', $schoolId)->findOrFail($linkStudentId);
            $student->guardians()->syncWithoutDetaching([$guardian->id => $pivotData]);
        }

        return redirect()->route('guardians.show', $guardian)
            ->with('success', "Guardian {$guardian->full_name} created successfully.");
    }

    public function show(Guardian $guardian): View
    {
        $this->authorizeSchoolAccess($guardian);
        $guardian->load(['students.currentClass', 'user']);
        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian): View
    {
        $this->authorizeSchoolAccess($guardian);
        return view('guardians.edit', compact('guardian'));
    }

    public function update(Request $request, Guardian $guardian): RedirectResponse
    {
        $this->authorizeSchoolAccess($guardian);

        $validated = $request->validate([
            'title'                => ['nullable', 'string', 'max:20'],
            'first_name'           => ['required', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'other_names'          => ['nullable', 'string', 'max:100'],
            'gender'               => ['nullable', 'in:male,female'],
            'relationship'         => ['nullable', 'string', 'max:50'],
            'phone'                => ['required', 'string', 'max:30'],
            'alt_phone'            => ['nullable', 'string', 'max:30'],
            'email'                => ['nullable', 'email', 'max:150'],
            'occupation'           => ['nullable', 'string', 'max:150'],
            'employer'             => ['nullable', 'string', 'max:200'],
            'address'              => ['nullable', 'string'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'state'                => ['nullable', 'string', 'max:100'],
            'nationality'          => ['nullable', 'string', 'max:100'],
            'national_id'          => ['nullable', 'string', 'max:50'],
            'is_primary_contact'   => ['boolean'],
            'is_emergency_contact' => ['boolean'],
            'status'               => ['required', 'in:active,inactive'],
        ]);

        $guardian->update($validated);

        return redirect()->route('guardians.show', $guardian)
            ->with('success', 'Guardian updated successfully.');
    }

    public function togglePortal(Guardian $guardian): RedirectResponse
    {
        $this->authorizeSchoolAccess($guardian);
        $guardian->update(['portal_access' => !$guardian->portal_access]);
        $state = $guardian->portal_access ? 'enabled' : 'disabled';
        return back()->with('success', "Portal access {$state} for {$guardian->full_name}.");
    }

    public function destroy(Guardian $guardian): RedirectResponse
    {
        $this->authorizeSchoolAccess($guardian);
        $name = $guardian->full_name;
        $guardian->delete();
        return redirect()->route('guardians.index')
            ->with('success', "Guardian {$name} has been removed.");
    }

    // ---------------------------------------------------------------
    // Student ↔ Guardian linking (called from student profile)
    // ---------------------------------------------------------------

    public function attachStudent(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentAccess($student);

        $validated = $request->validate([
            'guardian_id'      => ['required', 'exists:guardians,id'],
            'relationship'     => ['nullable', 'string', 'max:50'],
            'is_primary'       => ['boolean'],
            'is_emergency'     => ['boolean'],
            'can_pickup'       => ['boolean'],
            'receives_reports' => ['boolean'],
            'receives_invoices'=> ['boolean'],
        ]);

        $guardianId = $validated['guardian_id'];
        unset($validated['guardian_id']);

        // Cast booleans explicitly (checkboxes send strings)
        foreach (['is_primary','is_emergency','can_pickup','receives_reports','receives_invoices'] as $field) {
            $validated[$field] = (bool) ($validated[$field] ?? false);
        }

        $student->guardians()->syncWithoutDetaching([$guardianId => $validated]);

        return back()->with('success', 'Guardian linked to student.');
    }

    public function detachStudent(Student $student, Guardian $guardian): RedirectResponse
    {
        $this->authorizeStudentAccess($student);
        $student->guardians()->detach($guardian->id);
        return back()->with('success', 'Guardian unlinked from student.');
    }

    public function updatePivot(Request $request, Student $student, Guardian $guardian): RedirectResponse
    {
        $this->authorizeStudentAccess($student);

        $validated = $request->validate([
            'relationship'     => ['nullable', 'string', 'max:50'],
            'is_primary'       => ['boolean'],
            'is_emergency'     => ['boolean'],
            'can_pickup'       => ['boolean'],
            'receives_reports' => ['boolean'],
            'receives_invoices'=> ['boolean'],
        ]);

        foreach (['is_primary','is_emergency','can_pickup','receives_reports','receives_invoices'] as $field) {
            $validated[$field] = (bool) ($validated[$field] ?? false);
        }

        $student->guardians()->updateExistingPivot($guardian->id, $validated);

        return back()->with('success', 'Guardian relationship updated.');
    }

    private function authorizeSchoolAccess(Guardian $guardian): void
    {
        if ($guardian->school_id != auth()->user()->school_id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }
    }

    private function authorizeStudentAccess(Student $student): void
    {
        if ($student->school_id != auth()->user()->school_id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }
    }
}
