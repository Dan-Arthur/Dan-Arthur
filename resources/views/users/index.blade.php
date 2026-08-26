@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage staff accounts, roles, and access control</p>
    </div>
    @can('create users')
    <a href="{{ route('users.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add User
    </a>
    @endcan
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-gray-500">Total Users</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['active']) }}</p>
            <p class="text-xs text-gray-500">Active</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['inactive']) }}</p>
            <p class="text-xs text-gray-500">Inactive / Suspended</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input" placeholder="Name, email, phone…">
        </div>
        <div class="w-44">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active"    @selected(request('status') === 'active')>Active</option>
                <option value="inactive"  @selected(request('status') === 'inactive')>Inactive</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','role','status']))
        <a href="{{ route('users.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="{{ !$user->isActive() ? 'opacity-60' : '' }}">
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                    <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 px-1.5 py-0.5 rounded font-medium">You</span>
                                    @endif
                                    @if($user->isSuperAdmin())
                                    <span class="badge badge-purple text-xs">Super Admin</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        @foreach($user->roles as $role)
                        <span class="badge badge-info text-xs">
                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                        </span>
                        @endforeach
                    </td>
                    <td class="text-sm text-gray-600 dark:text-gray-400">{{ $user->phone ?? '—' }}</td>
                    <td class="text-sm text-gray-500">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td>
                        @php
                        $sc = ['active'=>'badge-success','inactive'=>'badge-gray','suspended'=>'badge-danger'][$user->status] ?? 'badge-gray';
                        @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst($user->status) }}</span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('users.show', $user) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                            @can('edit users')
                            @if($user->id !== auth()->id() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('users.edit', $user) }}"
                               class="text-gray-500 hover:text-gray-700 text-sm">Edit</a>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p class="font-medium">No users found</p>
                        @can('create users')
                        <a href="{{ route('users.create') }}" class="text-blue-600 text-sm mt-1 inline-block">Add the first user</a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
