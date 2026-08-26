<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\SystemSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    private function gate(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);
    }

    private function school()
    {
        return auth()->user()->school;
    }

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeSchool(AcademicYear|Campus|Term $model): void
    {
        $schoolId = $model instanceof Term
            ? $model->academicYear->school_id
            : $model->school_id;

        abort_unless($schoolId === $this->schoolId(), 403);
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $this->gate();

        $school   = $this->school();
        $schoolId = $school->id;
        $tab      = in_array($request->tab, ['school', 'years', 'campuses', 'system'])
            ? $request->tab
            : 'school';

        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->with(['terms' => fn($q) => $q->orderBy('sequence')])
            ->orderByDesc('start_date')
            ->get();

        $campuses = Campus::where('school_id', $schoolId)
            ->orderByDesc('is_main_campus')
            ->orderBy('name')
            ->withCount('students')
            ->get();

        $systemSettings = SystemSetting::where('school_id', $schoolId)
            ->get()
            ->keyBy('key');

        return view('settings.index', compact(
            'school', 'academicYears', 'campuses', 'systemSettings', 'tab'
        ));
    }

    // ---------------------------------------------------------------
    // SCHOOL PROFILE
    // ---------------------------------------------------------------

    public function updateSchool(Request $request)
    {
        $this->gate();
        $school = $this->school();

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:200'],
            'code'               => ['nullable', 'string', 'max:20'],
            'type'               => ['nullable', Rule::in(['private', 'public', 'faith-based', 'international'])],
            'motto'              => ['nullable', 'string', 'max:200'],
            'address'            => ['nullable', 'string', 'max:500'],
            'city'               => ['nullable', 'string', 'max:100'],
            'state'              => ['nullable', 'string', 'max:100'],
            'country'            => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:20'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'email'              => ['nullable', 'email', 'max:150'],
            'website'            => ['nullable', 'url', 'max:200'],
            'logo'               => ['nullable', 'image', 'max:2048'],
            'academic_structure' => ['nullable', Rule::in(['semester', 'trimester', 'term', 'quarter'])],
            'terms_per_year'     => ['nullable', 'integer', 'min:1', 'max:6'],
            'currency_code'      => ['nullable', 'string', 'max:10'],
            'currency_symbol'    => ['nullable', 'string', 'max:10'],
        ]);

        if ($request->hasFile('logo')) {
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($validated['logo']);
        }

        $school->update($validated);

        return redirect()->route('settings.index', ['tab' => 'school'])
            ->with('success', 'School profile saved.');
    }

    // ---------------------------------------------------------------
    // ACADEMIC YEARS
    // ---------------------------------------------------------------

    public function storeYear(Request $request)
    {
        $this->gate();
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:20',
                Rule::unique('academic_years')->where('school_id', $schoolId)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'status'     => ['required', Rule::in(['upcoming', 'active', 'completed'])],
        ]);

        AcademicYear::create(array_merge($validated, ['school_id' => $schoolId]));

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', "Academic year '{$validated['name']}' created.");
    }

    public function updateYear(Request $request, AcademicYear $year)
    {
        $this->gate();
        $this->authorizeSchool($year);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:20',
                Rule::unique('academic_years')->where('school_id', $year->school_id)->ignore($year->id)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'status'     => ['required', Rule::in(['upcoming', 'active', 'completed'])],
        ]);

        $year->update($validated);

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', 'Academic year updated.');
    }

    public function setCurrentYear(AcademicYear $year)
    {
        $this->gate();
        $schoolId = $this->schoolId();
        $this->authorizeSchool($year);

        DB::transaction(function () use ($year, $schoolId) {
            AcademicYear::where('school_id', $schoolId)->update(['is_current' => false]);
            $year->update(['is_current' => true, 'status' => 'active']);
        });

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', "'{$year->name}' is now the current academic year.");
    }

    public function destroyYear(AcademicYear $year)
    {
        $this->gate();
        $this->authorizeSchool($year);

        if ($year->is_current) {
            return redirect()->route('settings.index', ['tab' => 'years'])
                ->with('error', 'Cannot delete the current academic year.');
        }

        if ($year->enrolments()->exists()) {
            return redirect()->route('settings.index', ['tab' => 'years'])
                ->with('error', 'Cannot delete a year that has student enrolments.');
        }

        $year->terms()->delete();
        $year->delete();

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', 'Academic year deleted.');
    }

    // ---------------------------------------------------------------
    // TERMS
    // ---------------------------------------------------------------

    public function storeTerm(Request $request, AcademicYear $year)
    {
        $this->gate();
        $this->authorizeSchool($year);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:50'],
            'sequence'            => ['required', 'integer', 'min:1',
                Rule::unique('terms')->where('academic_year_id', $year->id)],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after:start_date'],
            'result_release_date' => ['nullable', 'date'],
            'status'              => ['required', Rule::in(['upcoming', 'active', 'completed'])],
        ]);

        Term::create(array_merge($validated, ['academic_year_id' => $year->id]));

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', "Term '{$validated['name']}' added to {$year->name}.");
    }

    public function updateTerm(Request $request, Term $term)
    {
        $this->gate();
        $this->authorizeSchool($term);
        $year = $term->academicYear;

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:50'],
            'sequence'            => ['required', 'integer', 'min:1',
                Rule::unique('terms')->where('academic_year_id', $year->id)->ignore($term->id)],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after:start_date'],
            'result_release_date' => ['nullable', 'date'],
            'status'              => ['required', Rule::in(['upcoming', 'active', 'completed'])],
        ]);

        $term->update($validated);

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', 'Term updated.');
    }

    public function setCurrentTerm(Term $term)
    {
        $this->gate();
        $this->authorizeSchool($term);
        $year = $term->academicYear;

        DB::transaction(function () use ($term, $year) {
            Term::where('academic_year_id', $year->id)->update(['is_current' => false]);
            $term->update(['is_current' => true, 'status' => 'active']);
            AcademicYear::where('school_id', $year->school_id)->update(['is_current' => false]);
            $year->update(['is_current' => true, 'status' => 'active']);
        });

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', "'{$term->name}' ({$year->name}) is now the current term.");
    }

    public function destroyTerm(Term $term)
    {
        $this->gate();
        $this->authorizeSchool($term);

        if ($term->is_current) {
            return redirect()->route('settings.index', ['tab' => 'years'])
                ->with('error', 'Cannot delete the current term.');
        }

        $term->delete();

        return redirect()->route('settings.index', ['tab' => 'years'])
            ->with('success', 'Term deleted.');
    }

    // ---------------------------------------------------------------
    // CAMPUSES
    // ---------------------------------------------------------------

    public function storeCampus(Request $request)
    {
        $this->gate();
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['required', 'string', 'max:20',
                Rule::unique('campuses')->where('school_id', $schoolId)],
            'address'        => ['nullable', 'string', 'max:500'],
            'city'           => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'is_active'      => ['nullable', 'boolean'],
            'is_main_campus' => ['nullable', 'boolean'],
        ]);

        $validated['school_id']      = $schoolId;
        $validated['is_active']      = $request->boolean('is_active');
        $validated['is_main_campus'] = $request->boolean('is_main_campus');

        Campus::create($validated);

        return redirect()->route('settings.index', ['tab' => 'campuses'])
            ->with('success', "Campus '{$validated['name']}' created.");
    }

    public function updateCampus(Request $request, Campus $campus)
    {
        $this->gate();
        $this->authorizeSchool($campus);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['required', 'string', 'max:20',
                Rule::unique('campuses')->where('school_id', $campus->school_id)->ignore($campus->id)],
            'address'        => ['nullable', 'string', 'max:500'],
            'city'           => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'is_active'      => ['nullable', 'boolean'],
            'is_main_campus' => ['nullable', 'boolean'],
        ]);

        $validated['is_active']      = $request->boolean('is_active');
        $validated['is_main_campus'] = $request->boolean('is_main_campus');

        $campus->update($validated);

        return redirect()->route('settings.index', ['tab' => 'campuses'])
            ->with('success', "Campus updated.");
    }

    public function destroyCampus(Campus $campus)
    {
        $this->gate();
        $this->authorizeSchool($campus);

        if ($campus->is_main_campus) {
            return redirect()->route('settings.index', ['tab' => 'campuses'])
                ->with('error', 'Cannot delete the main campus.');
        }

        if ($campus->students()->count() > 0) {
            return redirect()->route('settings.index', ['tab' => 'campuses'])
                ->with('error', 'Cannot delete a campus that has students assigned to it.');
        }

        $campus->delete();

        return redirect()->route('settings.index', ['tab' => 'campuses'])
            ->with('success', 'Campus deleted.');
    }

    // ---------------------------------------------------------------
    // SYSTEM SETTINGS
    // ---------------------------------------------------------------

    public function updateSystemSettings(Request $request)
    {
        $this->gate();
        $schoolId = $this->schoolId();

        $request->validate([
            'admission_number_prefix'  => ['nullable', 'string', 'max:20', 'alpha_num'],
            'student_number_prefix'    => ['nullable', 'string', 'max:20', 'alpha_num'],
            'invoice_due_days'         => ['nullable', 'integer', 'min:1', 'max:365'],
            'attendance_start_time'    => ['nullable', 'date_format:H:i'],
            'late_threshold_minutes'   => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $map = [
            'admission_number_prefix'  => ['group' => 'admission',  'type' => 'string'],
            'student_number_prefix'    => ['group' => 'student',    'type' => 'string'],
            'invoice_due_days'         => ['group' => 'finance',    'type' => 'integer'],
            'result_approval_required' => ['group' => 'academic',   'type' => 'boolean'],
            'show_position_on_result'  => ['group' => 'academic',   'type' => 'boolean'],
            'attendance_start_time'    => ['group' => 'attendance', 'type' => 'string'],
            'late_threshold_minutes'   => ['group' => 'attendance', 'type' => 'integer'],
        ];

        foreach ($map as $key => $meta) {
            if ($meta['type'] === 'boolean') {
                $value = $request->boolean($key) ? 'true' : 'false';
            } else {
                $value = $request->input($key);
                if ($value === null || $value === '') continue;
            }

            SystemSetting::set($key, $value, $schoolId, $meta['type'], $meta['group']);
        }

        return redirect()->route('settings.index', ['tab' => 'system'])
            ->with('success', 'System settings saved.');
    }
}
