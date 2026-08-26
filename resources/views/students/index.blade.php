@extends('layouts.app')

@section('title', 'Students')

@section('breadcrumbs')
<a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
<span class="text-gray-400 mx-1">/</span>
<span class="text-gray-900 font-medium">Students</span>
@endsection

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Students</h1>
        <p class="page-subtitle">Manage student records, enrolments and profiles</p>
    </div>
    @can('create students')
    <div class="flex gap-2">
        <button class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </button>
        <a href="{{ route('students.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Student
        </a>
    </div>
    @endcan
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="kpi-card !p-4">
        <p class="kpi-value text-2xl">{{ number_format($stats['total']) }}</p>
        <p class="kpi-label text-xs">Total Students</p>
    </div>
    <div class="kpi-card !p-4">
        <p class="kpi-value text-2xl text-green-600">{{ number_format($stats['active']) }}</p>
        <p class="kpi-label text-xs">Active</p>
    </div>
    <div class="kpi-card !p-4">
        <p class="kpi-value text-2xl text-blue-600">{{ number_format($stats['male']) }}</p>
        <p class="kpi-label text-xs">Male</p>
    </div>
    <div class="kpi-card !p-4">
        <p class="kpi-value text-2xl text-pink-600">{{ number_format($stats['female']) }}</p>
        <p class="kpi-label text-xs">Female</p>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <form method="GET" action="{{ route('students.index') }}" class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, number, email..." class="form-input pl-9">
                </div>
            </div>
            <select name="class_id" class="form-select">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
            </select>
            <select name="gender" class="form-select">
                <option value="">All Genders</option>
                <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
            </select>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            @if(request()->hasAny(['search', 'class_id', 'status', 'gender']))
            <a href="{{ route('students.index') }}" class="btn-secondary btn-sm">Clear</a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-8"><input type="checkbox" class="rounded border-gray-300"></th>
                    <th>
                        <a href="{{ route('students.index', array_merge(request()->query(), ['sort' => 'first_name', 'dir' => request('sort') === 'first_name' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                            Student
                            @if(request('sort') === 'first_name')<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="{{ request('dir') === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z' }}"/></svg>@endif
                        </a>
                    </th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Admitted</th>
                    <th class="w-20">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td><input type="checkbox" class="rounded border-gray-300"></td>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                            <div>
                                <a href="{{ route('students.show', $student) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                    {{ $student->full_name }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $student->student_number }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $student->currentClass?->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $student->gender === 'male' ? 'badge-info' : 'badge-purple' }}">
                            {{ ucfirst($student->gender ?? 'N/A') }}
                        </span>
                    </td>
                    <td>
                        <p class="text-gray-700">{{ $student->phone ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $student->email ?? '' }}</p>
                    </td>
                    <td>
                        @php
                        $statusColors = [
                            'active' => 'badge-success',
                            'inactive' => 'badge-gray',
                            'graduated' => 'badge-purple',
                            'transferred' => 'badge-warning',
                            'withdrawn' => 'badge-danger',
                            'suspended' => 'badge-danger',
                        ];
                        @endphp
                        <span class="badge {{ $statusColors[$student->status] ?? 'badge-gray' }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td class="text-gray-500 text-xs">{{ $student->admission_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('students.show', $student) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="View">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @can('edit students')
                            <a href="{{ route('students.edit', $student) }}" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <p class="text-sm font-medium">No students found</p>
                        <p class="text-xs mt-1">
                            @if(request()->hasAny(['search', 'class_id', 'status', 'gender']))
                            Try adjusting your filters.
                            @else
                            <a href="{{ route('students.create') }}" class="text-blue-600 hover:underline">Add your first student</a>
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $students->links() }}
    </div>
    @endif
</div>

</div>
@endsection
