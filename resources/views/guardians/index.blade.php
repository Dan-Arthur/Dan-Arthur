@extends('layouts.app')

@section('title', 'Guardians')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Guardians</h1>
        <p class="page-subtitle">Parents and guardians linked to students</p>
    </div>
    @can('create guardians')
    <a href="{{ route('guardians.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Guardian
    </a>
    @endcan
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-gray-500">Total Guardians</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['active']) }}</p>
            <p class="text-xs text-gray-500">Active</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['portal_access']) }}</p>
            <p class="text-xs text-gray-500">Portal Access</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('guardians.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input" placeholder="Name, phone, email…">
        </div>
        <div class="w-36">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="w-36">
            <label class="form-label">Portal Access</label>
            <select name="portal" class="form-select">
                <option value="">Any</option>
                <option value="yes" @selected(request('portal') === 'yes')>Has Access</option>
                <option value="no" @selected(request('portal') === 'no')>No Access</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','status','portal']))
        <a href="{{ route('guardians.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Guardian</th>
                    <th>Phone</th>
                    <th>Relationship</th>
                    <th>Students</th>
                    <th>Portal</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guardians as $guardian)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $guardian->photo_url }}" alt="{{ $guardian->full_name }}"
                                class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $guardian->full_name }}</p>
                                @if($guardian->email)
                                <p class="text-xs text-gray-500">{{ $guardian->email }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-sm">
                        {{ $guardian->phone }}
                        @if($guardian->alt_phone)
                        <br><span class="text-gray-400 text-xs">{{ $guardian->alt_phone }}</span>
                        @endif
                    </td>
                    <td class="text-sm text-gray-600 dark:text-gray-400">{{ $guardian->relationship ?? '—' }}</td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @forelse($guardian->students->take(3) as $student)
                            <a href="{{ route('students.show', $student) }}"
                               class="text-xs bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full hover:bg-blue-100">
                                {{ $student->first_name }}
                            </a>
                            @empty
                            <span class="text-xs text-gray-400">None linked</span>
                            @endforelse
                            @if($guardian->students->count() > 3)
                            <span class="text-xs text-gray-400">+{{ $guardian->students->count() - 3 }} more</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($guardian->portal_access)
                        <span class="badge badge-success">Active</span>
                        @else
                        <span class="badge badge-gray">None</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $guardian->status === 'active' ? 'badge-success' : 'badge-gray' }}">
                            {{ ucfirst($guardian->status) }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('guardians.show', $guardian) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                            @can('edit guardians')
                            <a href="{{ route('guardians.edit', $guardian) }}"
                               class="text-gray-500 hover:text-gray-700 text-sm">Edit</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                        </svg>
                        <p class="font-medium">No guardians found</p>
                        <p class="text-sm mt-1">
                            @can('create guardians')
                            <a href="{{ route('guardians.create') }}" class="text-blue-600">Add the first guardian</a>
                            @else
                            Adjust filters or check back later.
                            @endcan
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($guardians->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $guardians->links() }}
    </div>
    @endif
</div>
@endsection
