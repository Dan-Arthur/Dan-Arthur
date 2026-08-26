@extends('layouts.app')

@section('title', $department->name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('departments.index') }}" class="hover:text-blue-600">Departments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $department->name }}</span>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="page-title">{{ $department->name }}</h1>
            @if(!$department->is_active)
            <span class="badge badge-gray">Inactive</span>
            @endif
        </div>
    </div>
    @can('edit departments')
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('departments.toggle-active', $department) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn-secondary">
                {{ $department->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('departments.edit', $department) }}" class="btn-primary">Edit</a>
        @can('delete departments')
        <form method="POST" action="{{ route('departments.destroy', $department) }}"
            onsubmit="return confirm('Delete department {{ addslashes($department->name) }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
        @endcan
    </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Details sidebar --}}
    <div class="space-y-5">
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Details</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Code</span>
                    <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $department->code }}</span>
                </div>
                @if($department->type)
                <div class="flex justify-between">
                    <span class="text-gray-500">Type</span>
                    <span class="text-gray-900 dark:text-white">{{ $department->type }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Campus</span>
                    <span class="text-gray-900 dark:text-white">{{ $department->campus->name ?? 'All Campuses' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Head</span>
                    <span class="text-gray-900 dark:text-white">{{ $department->head->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Subjects</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $department->subjects->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="badge {{ $department->is_active ? 'badge-success' : 'badge-gray' }}">
                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        @if($department->description)
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $department->description }}</p>
        </div>
        @endif
    </div>

    {{-- Subjects list --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Subjects ({{ $department->subjects->count() }})
                </h3>
                @can('create subjects')
                <a href="{{ route('subjects.create', ['department_id' => $department->id]) }}"
                    class="btn-primary text-xs py-1.5 px-3">Add Subject</a>
                @endcan
            </div>
            @if($department->subjects->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">
                No subjects assigned to this department yet.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left px-6 py-2 text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Credits</th>
                            <th class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($department->subjects as $subject)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('subjects.show', $subject) }}"
                                        class="font-medium text-gray-900 dark:text-white hover:text-blue-600">
                                        {{ $subject->name }}
                                    </a>
                                    @if($subject->has_practical)
                                    <span class="badge badge-warning text-xs">Lab</span>
                                    @endif
                                    @if(!$subject->is_active)
                                    <span class="badge badge-gray text-xs">Inactive</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $subject->code }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $subject->type_color }} text-xs">{{ $subject->type_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-center">{{ $subject->credit_hours }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('subjects.show', $subject) }}" class="icon-btn" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
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
