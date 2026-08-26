<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view users')->only(['index', 'show']);
        $this->middleware('can:create users')->only(['create', 'store']);
        $this->middleware('can:edit users')->only(['edit', 'update', 'toggleStatus', 'resetPassword']);
        $this->middleware('can:delete users')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $auth     = auth()->user();
        $schoolId = $auth->school_id;

        $query = User::with('roles')
            ->when(!$auth->isSuperAdmin(), fn($q) => $q->where('school_id', $schoolId))
            ->when($auth->isSuperAdmin() && $request->school_id, fn($q) =>
                $q->where('school_id', $request->school_id)
            )
            ->when($request->search, fn($q, $s) =>
                $q->where(function ($inner) use ($s) {
                    $inner->where('name', 'like', "%{$s}%")
                          ->orWhere('email', 'like', "%{$s}%")
                          ->orWhere('phone', 'like', "%{$s}%");
                })
            )
            ->when($request->role, fn($q, $r) =>
                $q->whereHas('roles', fn($inner) => $inner->where('name', $r))
            )
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('name');

        $users = $query->paginate(20)->withQueryString();

        $roles = $this->availableRoles($auth);

        $stats = [
            'total'    => User::when(!$auth->isSuperAdmin(), fn($q) => $q->where('school_id', $schoolId))->count(),
            'active'   => User::when(!$auth->isSuperAdmin(), fn($q) => $q->where('school_id', $schoolId))->where('status', 'active')->count(),
            'inactive' => User::when(!$auth->isSuperAdmin(), fn($q) => $q->where('school_id', $schoolId))->whereIn('status', ['inactive', 'suspended'])->count(),
        ];

        return view('users.index', compact('users', 'roles', 'stats'));
    }

    public function create(): View
    {
        $auth     = auth()->user();
        $schoolId = $auth->school_id;
        $roles    = $this->availableRoles($auth);
        $campuses = Campus::where('school_id', $schoolId)->get();

        return view('users.create', compact('roles', 'campuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth     = auth()->user();
        $schoolId = $auth->school_id;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'campus_id'  => ['nullable', 'exists:campuses,id'],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'password'   => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'status'     => ['required', 'in:active,inactive'],
        ]);

        // Prevent escalation — non-super-admins can't create super-admin
        if ($validated['role'] === 'super-admin' && !$auth->isSuperAdmin()) {
            abort(403, 'Cannot assign super-admin role.');
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'name'       => trim("{$validated['first_name']} {$validated['last_name']}"),
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'campus_id'  => $validated['campus_id'] ?? null,
            'school_id'  => $schoolId,
            'password'   => Hash::make($validated['password']),
            'status'     => $validated['status'],
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.show', $user)
            ->with('success', "{$user->name} has been created and assigned the {$validated['role']} role.");
    }

    public function show(User $user): View
    {
        $this->authorizeAccess($user);
        $user->load('roles', 'campus', 'school');

        $loginHistory = LoginHistory::where('user_id', $user->id)
            ->orderByDesc('logged_in_at')
            ->limit(10)
            ->get();

        $roles = $this->availableRoles(auth()->user());

        return view('users.show', compact('user', 'loginHistory', 'roles'));
    }

    public function edit(User $user): View
    {
        $this->authorizeAccess($user);
        $auth     = auth()->user();
        $schoolId = $auth->school_id;
        $roles    = $this->availableRoles($auth);
        $campuses = Campus::where('school_id', $schoolId)->get();

        return view('users.edit', compact('user', 'roles', 'campuses'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAccess($user);
        $auth = auth()->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', "unique:users,email,{$user->id}"],
            'phone'      => ['nullable', 'string', 'max:30'],
            'campus_id'  => ['nullable', 'exists:campuses,id'],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'status'     => ['required', 'in:active,inactive,suspended'],
            'password'   => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ($validated['role'] === 'super-admin' && !$auth->isSuperAdmin()) {
            abort(403, 'Cannot assign super-admin role.');
        }

        // Prevent self-deactivation
        if ($user->id === $auth->id && $validated['status'] !== 'active') {
            return back()->withInput()->with('error', 'You cannot deactivate your own account.');
        }

        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'name'       => trim("{$validated['first_name']} {$validated['last_name']}"),
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'campus_id'  => $validated['campus_id'] ?? null,
            'status'     => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.show', $user)
            ->with('success', "{$user->name} updated successfully.");
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAccess($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $newStatus = $request->input('status', $user->status === 'active' ? 'inactive' : 'active');
        $user->update(['status' => $newStatus]);

        return back()->with('success', "{$user->name} has been " . ucfirst($newStatus) . ".");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAccess($user);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Password for {$user->name} has been reset.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeAccess($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Cannot delete a super admin account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "{$name} has been removed.");
    }

    private function availableRoles(User $auth): \Illuminate\Database\Eloquent\Collection
    {
        $query = Role::orderBy('name');

        // Only super-admins can assign the super-admin role
        if (!$auth->isSuperAdmin()) {
            $query->where('name', '!=', 'super-admin');
        }

        return $query->get();
    }

    private function authorizeAccess(User $user): void
    {
        $auth = auth()->user();

        // Super-admins can access any user
        if ($auth->isSuperAdmin()) {
            return;
        }

        // Non-super-admins can't see super-admin accounts
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        // Must be same school
        if ($user->school_id !== $auth->school_id) {
            abort(403, 'Access denied.');
        }
    }
}
