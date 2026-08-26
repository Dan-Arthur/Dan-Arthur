@extends('layouts.app')

@section('title', $subject->name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('subjects.index') }}" class="hover:text-blue-600">Subjects</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $subject->name }}</span>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="page-title">{{ $subject->name }}</h1>
            <span class="badge {{ $subject->type_color }}">{{ $subject->type_label }}</span>
            @if(!$subject->is_active)
            <span class="badge badge-gray">Inactive</span>
            @endif
            @if($subject->has_practical)
            <span class="badge badge-warning">Lab</span>
            @endif
        </div>
    </div>
    @can('edit subjects')
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('subjects.toggle-active', $subject) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn-secondary">
                {{ $subject->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('subjects.edit', $subject) }}" class="btn-primary">Edit Subject</a>
        <form method="POST" action="{{ route('subjects.destroy', $subject) }}"
            onsubmit="return confirm('Delete subject {{ addslashes($subject->name) }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
    </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Subject info --}}
    <div class="space-y-5">
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Subject Details</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Code</span>
                    <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $subject->code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Type</span>
                    <span class="badge {{ $subject->type_color }}">{{ $subject->type_label }}</span>
                </div>
                @if($subject->category)
                <div class="flex justify-between">
                    <span class="text-gray-500">Category</span>
                    <span class="text-gray-900 dark:text-white">{{ $subject->category_label }}</span>
                </div>
                @endif
                @if($subject->department)
                <div class="flex justify-between">
                    <span class="text-gray-500">Department</span>
                    <span class="text-gray-900 dark:text-white">{{ $subject->department->name }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Credit Hours</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $subject->credit_hours }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Practical</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $subject->has_practical ? 'Yes' : 'No' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Assignments</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $assignments->flatten()->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Class Assignments --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Assign to class form --}}
        @can('edit subjects')
        <div class="card p-5" x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between text-sm font-semibold text-gray-700 dark:text-gray-300">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Assign to a Class
                </span>
                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <form method="POST" action="{{ route('subjects.assign', $subject) }}">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="form-label text-xs">Academic Year <span class="text-red-500">*</span></label>
                        <select name="academic_year_id" class="form-select text-sm" required>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($currentYear?->id == $year->id)>
                                {{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Class <span class="text-red-500">*</span></label>
                        <select name="class_id" class="form-select text-sm" required>
                            <option value="">— Select class —</option>
                            @foreach($classes as $class)
                            @if(!$assignedClassIds->contains($class->id))
                            <option value="{{ $class->id }}">{{ $class->full_name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Teacher</label>
                        <select name="teacher_id" class="form-select text-sm">
                            <option value="">— Unassigned —</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Periods / Week <span class="text-red-500">*</span></label>
                        <input type="number" name="periods_per_week" value="5" min="1" max="40"
                            class="form-input text-sm" required>
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="hidden" name="is_compulsory" value="0">
                            <input type="checkbox" name="is_compulsory" value="1" checked class="rounded">
                            Compulsory
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn-primary text-sm mt-3">Assign to Class</button>
                </form>
            </div>
        </div>
        @endcan

        {{-- Assignments grouped by year --}}
        @forelse($assignments as $yearId => $yearAssignments)
        @php $year = $yearAssignments->first()->academicYear; @endphp
        <div class="card overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ $year->name ?? 'Academic Year #'.$yearId }}
                </h3>
                @if($year?->is_current)
                <span class="badge badge-success text-xs">Current</span>
                @endif
                <span class="text-xs text-gray-400 ml-auto">{{ $yearAssignments->count() }} class(es)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left px-6 py-2 text-xs font-medium text-gray-500 uppercase">Class</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Teacher</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Periods/Wk</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Compulsory</th>
                            @can('edit subjects')
                            <th class="px-4 py-2 w-24"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($yearAssignments as $assignment)
                        <tr x-data="{ editing: false }">
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $assignment->schoolClass->full_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $assignment->teacher->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-center">
                                {{ $assignment->periods_per_week }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $assignment->is_compulsory ? 'badge-info' : 'badge-gray' }} text-xs">
                                    {{ $assignment->is_compulsory ? 'Yes' : 'Optional' }}
                                </span>
                            </td>
                            @can('edit subjects')
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="editing = !editing" class="text-xs text-blue-600 hover:underline">Edit</button>
                                    <form method="POST"
                                        action="{{ route('subjects.unassign', [$subject, $assignment]) }}"
                                        onsubmit="return confirm('Remove from {{ addslashes($assignment->schoolClass->full_name ?? '') }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:underline">Remove</button>
                                    </form>
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @can('edit subjects')
                        {{-- Inline edit row --}}
                        <tr x-show="editing" style="display:none" class="bg-gray-50 dark:bg-gray-800/40">
                            <td colspan="5" class="px-6 py-3">
                                <form method="POST"
                                    action="{{ route('subjects.assignment.update', [$subject, $assignment]) }}">
                                @csrf @method('PATCH')
                                <div class="flex flex-wrap gap-3 items-end">
                                    <div>
                                        <label class="form-label text-xs">Teacher</label>
                                        <select name="teacher_id" class="form-select text-sm">
                                            <option value="">— Unassigned —</option>
                                            @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                @selected($assignment->teacher_id == $teacher->id)>
                                                {{ $teacher->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Periods / Week</label>
                                        <input type="number" name="periods_per_week"
                                            value="{{ $assignment->periods_per_week }}"
                                            min="1" max="40" class="form-input text-sm w-24" required>
                                    </div>
                                    <div class="flex items-end pb-1">
                                        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                            <input type="hidden" name="is_compulsory" value="0">
                                            <input type="checkbox" name="is_compulsory" value="1"
                                                @checked($assignment->is_compulsory) class="rounded">
                                            Compulsory
                                        </label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="btn-primary text-sm py-1.5 px-3">Save</button>
                                        <button type="button" @click="editing = false"
                                            class="btn-secondary text-sm py-1.5 px-3">Cancel</button>
                                    </div>
                                </div>
                                </form>
                            </td>
                        </tr>
                        @endcan
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="card p-10 text-center text-gray-400 text-sm">
            This subject has not been assigned to any class yet.
        </div>
        @endforelse

    </div>
</div>
@endsection
