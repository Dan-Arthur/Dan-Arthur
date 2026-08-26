<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view staff'), 403);

        $schoolId = auth()->user()->school_id;

        $query = Employee::where('school_id', $schoolId)
            ->with(['position', 'department', 'campus'])
            ->withCount('leaveRequests');

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q
                ->where('first_name', 'like', $s)
                ->orWhere('last_name', 'like', $s)
                ->orWhere('employee_number', 'like', $s)
                ->orWhere('email', 'like', $s));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        if ($request->filled('position_type')) {
            $query->whereHas('position', fn($q) => $q->where('type', $request->position_type));
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->paginate(30)->withQueryString();

        $departments = Department::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $statuses    = Employee::STATUSES;
        $positionTypes = Position::TYPES;

        return view('employees.index', compact('employees', 'departments', 'statuses', 'positionTypes'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('create staff'), 403);

        $schoolId   = auth()->user()->school_id;
        $positions  = Position::where('school_id', $schoolId)->where('is_active', true)->orderBy('title')->get();
        $departments= Department::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $campuses   = Campus::where('school_id', $schoolId)->orderBy('name')->get();
        $users      = User::where('school_id', $schoolId)->orderBy('name')->get();

        return view('employees.create', compact('positions', 'departments', 'campuses', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('create staff'), 403);

        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'other_names'     => 'nullable|string|max:100',
            'title'           => 'nullable|string|max:20',
            'gender'          => 'nullable|in:male,female,other',
            'date_of_birth'   => 'nullable|date|before:today',
            'nationality'     => 'nullable|string|max:100',
            'national_id'     => 'nullable|string|max:50',
            'phone'           => 'nullable|string|max:30',
            'alt_phone'       => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:150',
            'address'         => 'nullable|string|max:500',
            'qualification'   => 'nullable|string|max:200',
            'specialisation'  => 'nullable|string|max:200',
            'years_experience'=> 'nullable|integer|min:0',
            'joining_date'    => 'nullable|date',
            'employment_type' => 'nullable|in:full_time,part_time,contract',
            'position_id'     => 'nullable|exists:positions,id',
            'department_id'   => 'nullable|exists:departments,id',
            'campus_id'       => 'nullable|exists:campuses,id',
            'user_id'         => 'nullable|exists:users,id',
            'basic_salary'    => 'nullable|numeric|min:0',
            'bank_name'       => 'nullable|string|max:150',
            'bank_account'    => 'nullable|string|max:50',
            'bank_sort_code'  => 'nullable|string|max:20',
        ]);

        $schoolId = auth()->user()->school_id;

        $employee = Employee::create(array_merge($validated, [
            'school_id'       => $schoolId,
            'employee_number' => $this->nextNumber($schoolId),
            'status'          => 'active',
            'employment_type' => $validated['employment_type'] ?? 'full_time',
        ]));

        return redirect()->route('employees.show', $employee)->with('success', 'Employee record created.');
    }

    public function show(Employee $employee): View
    {
        abort_unless(auth()->user()->can('view staff'), 403);
        abort_unless($employee->school_id == auth()->user()->school_id, 403);

        $employee->load(['position', 'department', 'campus', 'user',
            'leaveRequests' => fn($q) => $q->with('leaveType')->latest()->limit(10)]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        abort_unless(auth()->user()->can('edit staff'), 403);
        abort_unless($employee->school_id == auth()->user()->school_id, 403);

        $schoolId   = auth()->user()->school_id;
        $positions  = Position::where('school_id', $schoolId)->where('is_active', true)->orderBy('title')->get();
        $departments= Department::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $campuses   = Campus::where('school_id', $schoolId)->orderBy('name')->get();
        $users      = User::where('school_id', $schoolId)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'positions', 'departments', 'campuses', 'users'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless(auth()->user()->can('edit staff'), 403);
        abort_unless($employee->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'other_names'     => 'nullable|string|max:100',
            'title'           => 'nullable|string|max:20',
            'gender'          => 'nullable|in:male,female,other',
            'date_of_birth'   => 'nullable|date|before:today',
            'nationality'     => 'nullable|string|max:100',
            'national_id'     => 'nullable|string|max:50',
            'phone'           => 'nullable|string|max:30',
            'alt_phone'       => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:150',
            'address'         => 'nullable|string|max:500',
            'qualification'   => 'nullable|string|max:200',
            'specialisation'  => 'nullable|string|max:200',
            'years_experience'=> 'nullable|integer|min:0',
            'joining_date'    => 'nullable|date',
            'employment_type' => 'nullable|in:full_time,part_time,contract',
            'status'          => 'required|in:' . implode(',', array_keys(Employee::STATUSES)),
            'exit_date'       => 'nullable|date',
            'exit_reason'     => 'nullable|string|max:500',
            'position_id'     => 'nullable|exists:positions,id',
            'department_id'   => 'nullable|exists:departments,id',
            'campus_id'       => 'nullable|exists:campuses,id',
            'user_id'         => 'nullable|exists:users,id',
            'basic_salary'    => 'nullable|numeric|min:0',
            'bank_name'       => 'nullable|string|max:150',
            'bank_account'    => 'nullable|string|max:50',
            'bank_sort_code'  => 'nullable|string|max:20',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        abort_unless(auth()->user()->can('delete staff'), 403);
        abort_unless($employee->school_id == auth()->user()->school_id, 403);

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee record removed.');
    }

    // ============================================================
    // POSITIONS
    // ============================================================

    public function positions(Request $request): View
    {
        abort_unless(auth()->user()->can('view positions'), 403);

        $schoolId  = auth()->user()->school_id;
        $positions = Position::where('school_id', $schoolId)
            ->with('department')
            ->withCount('employees')
            ->orderBy('type')
            ->orderBy('title')
            ->get();

        $departments = Department::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('employees.positions', compact('positions', 'departments'));
    }

    public function storePosition(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage positions'), 403);

        $validated = $request->validate([
            'title'         => 'required|string|max:150',
            'code'          => 'nullable|string|max:30',
            'type'          => 'required|in:teaching,non_teaching,management',
            'department_id' => 'nullable|exists:departments,id',
            'description'   => 'nullable|string|max:500',
        ]);

        Position::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
            'is_active' => true,
        ]));

        return redirect()->route('employees.positions')->with('success', 'Position added.');
    }

    public function updatePosition(Request $request, Position $position): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage positions'), 403);
        abort_unless($position->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'title'         => 'required|string|max:150',
            'code'          => 'nullable|string|max:30',
            'type'          => 'required|in:teaching,non_teaching,management',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $position->update($validated);

        return redirect()->route('employees.positions')->with('success', 'Position updated.');
    }

    public function destroyPosition(Position $position): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage positions'), 403);
        abort_unless($position->school_id == auth()->user()->school_id, 403);

        if ($position->employees()->exists()) {
            return back()->with('error', 'Cannot delete a position that has employees assigned to it.');
        }

        $position->delete();

        return redirect()->route('employees.positions')->with('success', 'Position deleted.');
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $schoolId = auth()->user()->school_id;
        $q = '%' . $request->get('q', '') . '%';

        $employees = Employee::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where(fn($query) => $query
                ->where('first_name', 'like', $q)
                ->orWhere('last_name', 'like', $q)
                ->orWhere('employee_number', 'like', $q))
            ->limit(15)
            ->get()
            ->map(fn($e) => [
                'id'              => $e->id,
                'full_name'       => $e->full_name,
                'employee_number' => $e->employee_number,
            ]);

        return response()->json($employees);
    }

    private function nextNumber(int $schoolId): string
    {
        $year = date('Y');
        $last = Employee::where('school_id', $schoolId)
            ->where('employee_number', 'like', "EMP{$year}%")
            ->count();
        return 'EMP' . $year . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
