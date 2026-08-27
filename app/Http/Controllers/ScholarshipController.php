<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\StudentScholarship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    // ── Program CRUD ────────────────────────────────────────────────

    public function index(): View
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);

        $scholarships = Scholarship::where('school_id', $this->schoolId())
            ->withCount('studentScholarships')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('scholarships.index', compact('scholarships'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);

        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('scholarships.create', compact('currency'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percentage cannot exceed 100%.']);
        }

        Scholarship::create(array_merge($validated, [
            'school_id' => $this->schoolId(),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('scholarships.index')
            ->with('success', 'Scholarship created.');
    }

    public function edit(Scholarship $scholarship): View
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);
        abort_unless($scholarship->school_id == $this->schoolId(), 403);

        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('scholarships.edit', compact('scholarship', 'currency'));
    }

    public function update(Request $request, Scholarship $scholarship): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);
        abort_unless($scholarship->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percentage cannot exceed 100%.']);
        }

        $scholarship->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('scholarships.index')
            ->with('success', 'Scholarship updated.');
    }

    public function destroy(Scholarship $scholarship): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);
        abort_unless($scholarship->school_id == $this->schoolId(), 403);

        if ($scholarship->studentScholarships()->exists()) {
            return back()->with('error',
                'Cannot delete — this scholarship has been assigned to student(s). Deactivate it instead.');
        }

        $scholarship->delete();

        return redirect()->route('scholarships.index')
            ->with('success', 'Scholarship deleted.');
    }

    // ── Student assignment ───────────────────────────────────────────

    public function students(Scholarship $scholarship): View
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);
        abort_unless($scholarship->school_id == $this->schoolId(), 403);

        $schoolId = $this->schoolId();

        $years = AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')->get();

        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $selectedYearId = request()->integer('year_id', $currentYear?->id ?? 0);

        $assignments = StudentScholarship::where('scholarship_id', $scholarship->id)
            ->where('school_id', $schoolId)
            ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
            ->with(['student', 'academicYear', 'assignedBy'])
            ->orderByDesc('created_at')
            ->get();

        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('scholarships.students', compact(
            'scholarship', 'assignments', 'years', 'selectedYearId', 'currentYear', 'currency',
        ));
    }

    public function assign(Request $request, Scholarship $scholarship): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);
        abort_unless($scholarship->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'notes'            => 'nullable|string|max:500',
        ]);

        $schoolId = $this->schoolId();

        abort_unless(
            Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->exists(),
            403
        );

        $exists = StudentScholarship::where('student_id', $validated['student_id'])
            ->where('scholarship_id', $scholarship->id)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This student already has this scholarship for the selected year.');
        }

        StudentScholarship::create([
            'school_id'        => $schoolId,
            'student_id'       => $validated['student_id'],
            'scholarship_id'   => $scholarship->id,
            'academic_year_id' => $validated['academic_year_id'],
            'notes'            => $validated['notes'] ?? null,
            'assigned_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Scholarship assigned successfully.');
    }

    public function revoke(Scholarship $scholarship, StudentScholarship $studentScholarship): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);
        abort_unless($scholarship->school_id == $this->schoolId(), 403);
        abort_unless($studentScholarship->scholarship_id == $scholarship->id, 403);

        $studentScholarship->delete();

        return back()->with('success', 'Scholarship assignment removed.');
    }

    // ── JSON search used by assign form ─────────────────────────────

    public function searchStudents(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->user()->can('manage scholarships'), 403);

        $q = $request->get('q', '');
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
}
