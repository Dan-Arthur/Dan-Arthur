@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Departments</h1>
        <p class="page-subtitle">Manage academic and administrative departments</p>
    </div>
    @can('create departments')
    <a href="{{ route('departments.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Department
    </a>
    @endcan
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('departments.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label text-xs">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input text-sm" placeholder="Name or code…">
        </div>
        @if($campuses->count() > 1)
        <div>
            <label class="form-label text-xs">Campus</label>
            <select name="campus_id" class="form-select text-sm">
                <option value="">All Campuses</option>
                @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" @selected(request('campus_id') == $campus->id)>{{ $campus->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                <option value="active"   @selected(request('status','active')==='active')>Active</option>
                <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">Filter</button>
        @if(request()->hasAny(['search','campus_id']))
        <a href="{{ route('departments.index') }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="card overflow-hidden">
    @if($departments->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p class="text-gray-500 text-sm">No departments found.</p>
        @can('create departments')
        <a href="{{ route('departments.create') }}" class="btn-primary mt-4 inline-flex">Add First Department</a>
        @endcan
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Campus</th>
                    <th>Head</th>
                    <th>Subjects</th>
                    <th>Status</th>
                    <th class="w-24"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                <tr>
                    <td>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $department->name }}</p>
                        @if($department->description)
                        <p class="text-xs text-gray-400 truncate max-w-xs">{{ $department->description }}</p>
                        @endif
                    </td>
                    <td class="font-mono text-xs text-gray-500">{{ $department->code }}</td>
                    <td class="text-sm text-gray-500">{{ $department->type ?: '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $department->campus->name ?? '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $department->head->name ?? '—' }}</td>
                    <td class="text-sm text-gray-500 text-center">{{ $department->subjects_count }}</td>
                    <td>
                        @can('edit departments')
                        <form method="POST" action="{{ route('departments.toggle-active', $department) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="badge cursor-pointer {{ $department->is_active ? 'badge-success' : 'badge-gray' }}">
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        @else
                        <span class="badge {{ $department->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @endcan
                    </td>
                    <td>
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('departments.show', $department) }}" class="icon-btn" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @can('edit departments')
                            <a href="{{ route('departments.edit', $department) }}" class="icon-btn" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $departments->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
