@extends('layouts.app')

@section('title', 'Classes')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Classes</h1>
        <p class="page-subtitle">Manage class groups, streams, and teacher assignments</p>
    </div>
    @can('create classes')
    <a href="{{ route('classes.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Class
    </a>
    @endcan
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500">Total Classes</p>
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
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-500">Active Classes</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['enrolled']) }}</p>
            <p class="text-xs text-gray-500">Students Enrolled</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('classes.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input" placeholder="Name, code, section…">
        </div>
        @if($programmes->count() > 0)
        <div class="w-40">
            <label class="form-label">Programme</label>
            <select name="programme" class="form-select">
                <option value="">All</option>
                @foreach($programmes as $p)
                <option value="{{ $p }}" @selected(request('programme') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if($campuses->count() > 1)
        <div class="w-40">
            <label class="form-label">Campus</label>
            <select name="campus_id" class="form-select">
                <option value="">All Campuses</option>
                @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" @selected(request('campus_id') == $campus->id)>{{ $campus->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="w-32">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active"   @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','programme','campus_id','status']))
        <a href="{{ route('classes.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

{{-- Classes grid --}}
@if($classes->isEmpty())
<div class="card p-16 text-center">
    <svg class="w-14 h-14 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
    <p class="font-medium text-gray-700 dark:text-gray-300">No classes found</p>
    @can('create classes')
    <a href="{{ route('classes.create') }}" class="btn-primary mt-4 inline-flex">Create first class</a>
    @endcan
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($classes as $class)
    @php
    $enrolled  = $class->students_count;
    $capacity  = $class->capacity;
    $occupancy = $capacity > 0 ? min(100, (int) round(($enrolled / $capacity) * 100)) : 0;
    $barColor  = $occupancy >= 90 ? 'bg-red-500' : ($occupancy >= 75 ? 'bg-yellow-500' : 'bg-blue-500');
    @endphp
    <div class="card p-5 flex flex-col gap-4 {{ !$class->is_active ? 'opacity-60' : '' }}">
        {{-- Header --}}
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-gray-900 dark:text-white text-lg leading-tight">
                        {{ $class->full_name }}
                    </h3>
                    @if(!$class->is_active)
                    <span class="badge badge-gray text-xs">Inactive</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $class->code }}</p>
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('classes.show', $class) }}"
                   class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
                @can('edit classes')
                <a href="{{ route('classes.edit', $class) }}"
                   class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                @endcan
            </div>
        </div>

        {{-- Meta tags --}}
        <div class="flex flex-wrap gap-1.5">
            @if($class->programme)
            <span class="text-xs bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full">
                {{ $class->programme }}
            </span>
            @endif
            @if($class->campus)
            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">
                {{ $class->campus->name }}
            </span>
            @endif
            @if($class->room)
            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 px-2 py-0.5 rounded-full">
                Room {{ $class->room }}
            </span>
            @endif
        </div>

        {{-- Capacity bar --}}
        <div>
            <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>{{ $enrolled }} enrolled</span>
                <span>{{ $capacity }} capacity</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                <div class="{{ $barColor }} h-2 rounded-full transition-all"
                     style="width: {{ $occupancy }}%"></div>
            </div>
            <p class="text-right text-xs mt-1
                {{ $occupancy >= 90 ? 'text-red-500' : ($occupancy >= 75 ? 'text-yellow-500' : 'text-gray-400') }}">
                {{ $occupancy }}% full
            </p>
        </div>

        {{-- Teacher --}}
        @if($class->classTeacher)
        <div class="flex items-center gap-2 pt-1 border-t border-gray-100 dark:border-gray-700">
            <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 text-xs font-bold flex items-center justify-center flex-shrink-0">
                {{ strtoupper(substr($class->classTeacher->name, 0, 1)) }}
            </div>
            <span class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $class->classTeacher->name }}</span>
            <span class="text-xs text-gray-400 ml-auto">Class Teacher</span>
        </div>
        @else
        <div class="pt-1 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-400 italic">No class teacher assigned</p>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($classes->hasPages())
<div class="mt-6">{{ $classes->links() }}</div>
@endif
@endif
@endsection
