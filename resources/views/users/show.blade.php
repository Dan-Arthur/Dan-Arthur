@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('users.index') }}" class="hover:text-blue-600">Users</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $user->name }}</span>
        </div>
        <div class="flex items-center gap-3">
            <h1 class="page-title">{{ $user->name }}</h1>
            @php
            $sc = ['active'=>'badge-success','inactive'=>'badge-gray','suspended'=>'badge-danger'][$user->status] ?? 'badge-gray';
            @endphp
            <span class="badge {{ $sc }}">{{ ucfirst($user->status) }}</span>
            @if($user->isSuperAdmin())
            <span class="badge badge-purple">Super Admin</span>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-2">
        @can('edit users')
        @if($user->id !== auth()->id() || auth()->user()->isSuperAdmin())
        {{-- Status toggle --}}
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('users.toggle-status', $user) }}">
            @csrf @method('PATCH')
            @if($user->status === 'active')
            <input type="hidden" name="status" value="inactive">
            <button type="submit" class="btn-secondary"
                onclick="return confirm('Deactivate {{ addslashes($user->name) }}?')">
                Deactivate
            </button>
            @elseif($user->status === 'suspended')
            <input type="hidden" name="status" value="active">
            <button type="submit" class="btn-secondary">Unsuspend</button>
            @else
            <input type="hidden" name="status" value="active">
            <button type="submit" class="btn-secondary">Activate</button>
            @endif
        </form>
        @endif
        <a href="{{ route('users.edit', $user) }}" class="btn-primary">Edit User</a>
        @endif
        @endcan
        @can('delete users')
        @if($user->id !== auth()->id() && !$user->isSuperAdmin())
        <form method="POST" action="{{ route('users.destroy', $user) }}"
              onsubmit="return confirm('Permanently remove {{ addslashes($user->name) }}? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
        @endif
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Profile --}}
    <div class="space-y-5">
        <div class="card p-6 text-center">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                class="w-24 h-24 rounded-full mx-auto object-cover">
            <h2 class="font-bold text-gray-900 dark:text-white mt-3 text-lg">{{ $user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <div class="flex flex-wrap items-center justify-center gap-1.5 mt-3">
                @foreach($user->roles as $role)
                <span class="badge badge-info">
                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                </span>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Account Info</h3>
            <div class="space-y-2.5 text-sm">
                @if($user->phone)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <a href="tel:{{ $user->phone }}" class="text-blue-600 hover:underline">{{ $user->phone }}</a>
                </div>
                @endif
                @if($user->campus)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-gray-700 dark:text-gray-300">{{ $user->campus->name }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500">Last Login</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Account Created</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $user->created_at->format('d M Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Timezone</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->timezone }}</span>
                </div>
            </div>
        </div>

        {{-- Reset password form --}}
        @can('edit users')
        @if($user->id !== auth()->id())
        <div class="card p-5" x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300">
                <span>Reset Password</span>
                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-4 space-y-3">
                <form method="POST" action="{{ route('users.reset-password', $user) }}">
                    @csrf @method('PATCH')
                    <div class="space-y-3">
                        <div>
                            <label class="form-label text-xs">New Password</label>
                            <input type="password" name="password"
                                class="form-input text-sm" placeholder="Min. 8 chars" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                class="form-input text-sm" placeholder="Repeat password" required>
                        </div>
                        <button type="submit" class="btn-danger w-full justify-center text-sm"
                            onclick="return confirm('Reset password for {{ addslashes($user->name) }}?')">
                            Set New Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>

    {{-- RIGHT: Login history & permissions --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Permissions summary --}}
        <div class="card p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4 text-sm">
                Permissions
                <span class="text-gray-400 font-normal ml-1">(via {{ $user->roles->pluck('name')->implode(', ') ?: 'no role' }})</span>
            </h3>
            @php
            $perms = $user->getAllPermissions()->pluck('name')->sort()->values();
            $grouped = $perms->groupBy(fn($p) => explode(' ', $p, 2)[1] ?? 'other');
            @endphp
            @if($perms->isEmpty())
            <p class="text-sm text-gray-400 italic">No permissions assigned.</p>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-1 text-xs text-gray-600 dark:text-gray-400 max-h-56 overflow-y-auto pr-2">
                @foreach($perms as $perm)
                <div class="flex items-center gap-1.5 py-0.5">
                    <svg class="w-3 h-3 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ $perm }}
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Login history --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Recent Login Activity</h3>
            </div>
            @if($loginHistory->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">No login history recorded.</div>
            @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loginHistory as $log)
                        <tr>
                            <td class="text-sm">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $log->logged_in_at?->format('d M Y') }}
                                </p>
                                <p class="text-gray-400 text-xs">{{ $log->logged_in_at?->format('H:i:s') }}</p>
                            </td>
                            <td class="font-mono text-xs text-gray-600 dark:text-gray-400">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="text-sm text-gray-500">
                                @if($log->logged_out_at)
                                    @php
                                    $mins = $log->logged_in_at->diffInMinutes($log->logged_out_at);
                                    echo $mins >= 60 ? floor($mins/60).'h '.($mins%60).'m' : $mins.'m';
                                    @endphp
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->logged_out_at)
                                <span class="badge badge-gray text-xs">Logged out</span>
                                @else
                                <span class="badge badge-success text-xs">Active / No logout</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
