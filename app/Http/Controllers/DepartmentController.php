<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $query = Department::where('school_id', $schoolId)
            ->with(['campus', 'head'])
            ->withCount('subjects');

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%$search%")->orWhere('code', 'like', "%$search%"));
        }

        if ($campus = $request->campus_id) {
            $query->where('campus_id', $campus);
        }

        if ($request->get('status', 'active') === 'inactive') {
            $query->where('is_active', false);
        } else {
            $query->where('is_active', true);
        }

        $departments = $query->orderBy('name')->paginate(30)->withQueryString();
        $campuses = Campus::where('school_id', $schoolId)->orderBy('name')->get();

        return view('departments.index', compact('departments', 'campuses'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('create departments'), 403);

        $schoolId = auth()->user()->school_id;
        $campuses = Campus::where('school_id', $schoolId)->orderBy('name')->get();
        $heads    = $this->headCandidates($schoolId);

        return view('departments.create', compact('campuses', 'heads'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('create departments'), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'code'        => 'required|string|max:20',
            'campus_id'   => 'nullable|exists:campuses,id',
            'type'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'head_id'     => 'nullable|exists:users,id',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['is_active'] = true;

        $dept = Department::create($validated);

        return redirect()->route('departments.show', $dept)->with('success', 'Department created.');
    }

    public function show(Department $department): View
    {
        $this->authorizeSchool($department);

        $department->load(['campus', 'head', 'subjects' => fn($q) => $q->with('department')]);

        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        abort_unless(auth()->user()->can('edit departments'), 403);
        $this->authorizeSchool($department);

        $schoolId = auth()->user()->school_id;
        $campuses = Campus::where('school_id', $schoolId)->orderBy('name')->get();
        $heads    = $this->headCandidates($schoolId);

        return view('departments.edit', compact('department', 'campuses', 'heads'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        abort_unless(auth()->user()->can('edit departments'), 403);
        $this->authorizeSchool($department);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'code'        => 'required|string|max:20',
            'campus_id'   => 'nullable|exists:campuses,id',
            'type'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'head_id'     => 'nullable|exists:users,id',
        ]);

        $department->update($validated);

        return redirect()->route('departments.show', $department)->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        abort_unless(auth()->user()->can('delete departments'), 403);
        $this->authorizeSchool($department);

        if ($department->subjects()->exists()) {
            return back()->with('error', 'Cannot delete department that has subjects assigned to it.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }

    public function toggleActive(Department $department): RedirectResponse
    {
        abort_unless(auth()->user()->can('edit departments'), 403);
        $this->authorizeSchool($department);

        $department->update(['is_active' => !$department->is_active]);

        return back()->with('success', 'Department ' . ($department->is_active ? 'activated' : 'deactivated') . '.');
    }

    private function authorizeSchool(Department $department): void
    {
        abort_unless($department->school_id == auth()->user()->school_id, 403);
    }

    private function headCandidates(int $schoolId)
    {
        return User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', [
                'teacher', 'principal', 'vice-principal', 'school-admin',
            ]))
            ->orderBy('name')
            ->get();
    }
}
