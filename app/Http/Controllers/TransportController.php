<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Student;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use App\Models\TransportStop;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportController extends Controller
{
    // ============================================================
    // ROUTES
    // ============================================================

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view transport'), 403);

        $schoolId = auth()->user()->school_id;

        $routes = TransportRoute::where('school_id', $schoolId)
            ->with(['vehicle', 'driver.employee'])
            ->withCount(['students', 'stops'])
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where('school_id', $schoolId)->where('status', 'active')->orderBy('registration_number')->get();
        $drivers  = Driver::where('school_id', $schoolId)->where('status', 'active')->with('employee')->get();

        return view('transport.index', compact('routes', 'vehicles', 'drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage routes'), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:30',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'driver_id'   => 'nullable|exists:drivers,id',
            'direction'   => 'required|in:pickup,dropoff,both',
            'monthly_fee' => 'nullable|numeric|min:0',
        ]);

        TransportRoute::create(array_merge($validated, ['school_id' => auth()->user()->school_id, 'is_active' => true]));

        return redirect()->route('transport.index')->with('success', 'Route created.');
    }

    public function show(TransportRoute $route): View
    {
        abort_unless(auth()->user()->can('view transport'), 403);
        abort_unless($route->school_id == auth()->user()->school_id, 403);

        $schoolId    = auth()->user()->school_id;
        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();

        $route->load(['vehicle', 'driver.employee', 'stops',
            'students' => fn($q) => $q->where('academic_year_id', $currentYear?->id)->with('student', 'stop')]);

        $availableStudents = Student::where('school_id', $schoolId)
            ->whereDoesntHave('transportAssignments', fn($q) => $q->where('route_id', $route->id)
                ->where('academic_year_id', $currentYear?->id))
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        return view('transport.show', compact('route', 'availableStudents', 'currentYear'));
    }

    public function update(Request $request, TransportRoute $route): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage routes'), 403);
        abort_unless($route->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'driver_id'   => 'nullable|exists:drivers,id',
            'direction'   => 'required|in:pickup,dropoff,both',
            'monthly_fee' => 'nullable|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        $route->update($validated);

        return back()->with('success', 'Route updated.');
    }

    // ============================================================
    // STOPS
    // ============================================================

    public function storeStop(Request $request, TransportRoute $route): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage routes'), 403);
        abort_unless($route->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'address'      => 'nullable|string|max:300',
            'pickup_time'  => 'nullable|date_format:H:i',
            'dropoff_time' => 'nullable|date_format:H:i',
        ]);

        $seq = $route->stops()->max('sequence') + 1;
        $route->stops()->create(array_merge($validated, ['sequence' => $seq]));

        return back()->with('success', 'Stop added.');
    }

    public function destroyStop(TransportStop $stop): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage routes'), 403);
        $stop->delete();
        return back()->with('success', 'Stop removed.');
    }

    // ============================================================
    // STUDENT ASSIGNMENTS
    // ============================================================

    public function assignStudent(Request $request, TransportRoute $route): RedirectResponse
    {
        abort_unless(auth()->user()->can('assign transport'), 403);
        abort_unless($route->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'stop_id'          => 'nullable|exists:transport_stops,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'direction'        => 'required|in:pickup,dropoff,both',
        ]);

        StudentTransport::updateOrCreate(
            ['student_id' => $validated['student_id'], 'route_id' => $route->id, 'academic_year_id' => $validated['academic_year_id']],
            ['stop_id' => $validated['stop_id'] ?? null, 'direction' => $validated['direction'], 'status' => 'active']
        );

        return back()->with('success', 'Student assigned to route.');
    }

    public function removeStudent(StudentTransport $assignment): RedirectResponse
    {
        abort_unless(auth()->user()->can('assign transport'), 403);
        $assignment->delete();
        return back()->with('success', 'Student removed from route.');
    }

    // ============================================================
    // VEHICLES
    // ============================================================

    public function vehicles(Request $request): View
    {
        abort_unless(auth()->user()->can('view transport'), 403);

        $schoolId = auth()->user()->school_id;

        $vehicles = Vehicle::where('school_id', $schoolId)
            ->withCount('routes')
            ->orderBy('registration_number')
            ->get();

        $drivers = Driver::where('school_id', $schoolId)
            ->with('employee')
            ->get();

        $employees = Employee::where('school_id', $schoolId)->where('status', 'active')
            ->orderBy('last_name')->get();

        return view('transport.vehicles', compact('vehicles', 'drivers', 'employees'));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage vehicles'), 403);

        $validated = $request->validate([
            'registration_number'    => 'required|string|max:30',
            'make'                   => 'nullable|string|max:100',
            'model'                  => 'nullable|string|max:100',
            'year'                   => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'capacity'               => 'required|integer|min:1',
            'type'                   => 'required|in:bus,van,car',
            'insurance_expiry'       => 'nullable|date',
            'road_worthiness_expiry' => 'nullable|date',
        ]);

        Vehicle::create(array_merge($validated, ['school_id' => auth()->user()->school_id, 'status' => 'active']));

        return redirect()->route('transport.vehicles')->with('success', 'Vehicle added.');
    }

    public function updateVehicle(Request $request, Vehicle $vehicle): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage vehicles'), 403);
        abort_unless($vehicle->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'registration_number'    => 'required|string|max:30',
            'make'                   => 'nullable|string|max:100',
            'model'                  => 'nullable|string|max:100',
            'year'                   => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'type'                   => 'required|in:bus,van,car',
            'capacity'               => 'required|integer|min:1',
            'status'                 => 'required|in:active,inactive,maintenance',
            'insurance_expiry'       => 'nullable|date',
            'road_worthiness_expiry' => 'nullable|date',
            'last_service_date'      => 'nullable|date',
            'next_service_date'      => 'nullable|date',
        ]);

        $vehicle->update($validated);

        return redirect()->route('transport.vehicles')->with('success', 'Vehicle updated.');
    }

    public function updateDriver(Request $request, Driver $driver): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage vehicles'), 403);
        abort_unless($driver->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'licence_number' => 'nullable|string|max:50',
            'licence_class'  => 'nullable|string|max:20',
            'licence_expiry' => 'nullable|date',
            'status'         => 'required|in:active,inactive',
        ]);

        $driver->update($validated);

        return redirect()->route('transport.vehicles')->with('success', 'Driver updated.');
    }

    public function storeDriver(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage vehicles'), 403);

        $validated = $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'licence_number' => 'nullable|string|max:50',
            'licence_class'  => 'nullable|string|max:20',
            'licence_expiry' => 'nullable|date',
        ]);

        Driver::create(array_merge($validated, ['school_id' => auth()->user()->school_id, 'status' => 'active']));

        return redirect()->route('transport.vehicles')->with('success', 'Driver registered.');
    }
}
