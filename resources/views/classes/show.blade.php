@extends('layouts.app')

@section('title', $class->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('classes.index') }}" class="hover:text-blue-600">Classes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $class->full_name }}</span>
        </div>
        <div class="flex items-center gap-3">
            <h1 class="page-title">{{ $class->full_name }}</h1>
            <span class="badge {{ $class->is_active ? 'badge-success' : 'badge-gray' }}">
                {{ $class->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @can('edit classes')
        <form method="POST" action="{{ route('classes.toggle-active', $class) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn-secondary">
                {{ $class->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('classes.edit', $class) }}" class="btn-primary">Edit Class</a>
        @endcan
        @can('delete classes')
        @if($stats['enrolled'] === 0)
        <form method="POST" action="{{ route('classes.destroy', $class) }}"
              onsubmit="return confirm('Delete class {{ addslashes($class->full_name) }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
        @endif
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Info cards --}}
    <div class="space-y-5">

        {{-- Class details --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Class Details</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Code</span>
                    <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $class->code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Level</span>
                    <span class="text-gray-900 dark:text-white">{{ $class->level }}</span>
                </div>
                @if($class->section)
                <div class="flex justify-between">
                    <span class="text-gray-500">Section / Arm</span>
                    <span class="text-gray-900 dark:text-white">{{ $class->section }}</span>
                </div>
                @endif
                @if($class->programme)
                <div class="flex justify-between">
                    <span class="text-gray-500">Programme</span>
                    <span class="text-gray-900 dark:text-white">{{ $class->programme }}</span>
                </div>
                @endif
                @if($class->room)
                <div class="flex justify-between">
                    <span class="text-gray-500">Room</span>
                    <span class="text-gray-900 dark:text-white">{{ $class->room }}</span>
                </div>
                @endif
                @if($class->campus)
                <div class="flex justify-between">
                    <span class="text-gray-500">Campus</span>
                    <span class="text-gray-900 dark:text-white">{{ $class->campus->name }}</span>
                </div>
                @endif
                @if($class->department)
                <div class="flex justify-between">
                    <span class="text-gray-500">Department</span>
                    <span class="text-gray-900 dark:text-white">{{ $class->department->name }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Occupancy --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Occupancy</h3>

            {{-- Big number --}}
            <div class="text-center mb-4">
                <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $stats['enrolled'] }}</p>
                <p class="text-sm text-gray-500">of {{ $stats['capacity'] }} places filled</p>
            </div>

            {{-- Bar --}}
            @php
            $barColor = $stats['occupancy'] >= 90 ? 'bg-red-500' : ($stats['occupancy'] >= 75 ? 'bg-yellow-500' : 'bg-blue-500');
            @endphp
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 mb-2">
                <div class="{{ $barColor }} h-3 rounded-full" style="width: {{ $stats['occupancy'] }}%"></div>
            </div>
            <p class="text-center text-sm font-medium
                {{ $stats['occupancy'] >= 90 ? 'text-red-500' : ($stats['occupancy'] >= 75 ? 'text-yellow-500' : 'text-blue-600') }}">
                {{ $stats['occupancy'] }}% capacity used
            </p>

            <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-center text-sm">
                <div>
                    <p class="text-lg font-bold text-blue-600">{{ $stats['male'] }}</p>
                    <p class="text-xs text-gray-500">Male</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-pink-500">{{ $stats['female'] }}</p>
                    <p class="text-xs text-gray-500">Female</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-green-600">{{ $stats['available'] }}</p>
                    <p class="text-xs text-gray-500">Available</p>
                </div>
            </div>
        </div>

        {{-- Class teacher --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Class Teacher</h3>
                @can('edit classes')
                <a href="{{ route('classes.edit', $class) }}" class="text-xs text-blue-600 hover:underline">Change</a>
                @endcan
            </div>
            @if($class->classTeacher)
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 font-bold text-sm flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($class->classTeacher->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $class->classTeacher->name }}</p>
                    @if($class->classTeacher->email)
                    <a href="mailto:{{ $class->classTeacher->email }}"
                       class="text-xs text-blue-600 hover:underline">{{ $class->classTeacher->email }}</a>
                    @endif
                </div>
            </div>
            @else
            <p class="text-sm text-gray-400 italic">No teacher assigned.</p>
            @can('edit classes')
            <a href="{{ route('classes.edit', $class) }}" class="btn-secondary btn-sm mt-3 text-xs">Assign Teacher</a>
            @endcan
            @endif
        </div>
    </div>

    {{-- RIGHT: Student roster --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Student Roster
                    <span class="text-gray-400 text-sm font-normal ml-1">({{ $stats['enrolled'] }})</span>
                </h3>
                @can('create students')
                <a href="{{ route('students.create') }}" class="btn-secondary btn-sm text-xs">+ Add Student</a>
                @endcan
            </div>

            @if($students->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-gray-500 font-medium">No students in this class yet.</p>
                <p class="text-sm text-gray-400 mt-1">Students assigned to this class will appear here.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>ID</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                        <tr>
                            <td class="text-gray-400 text-sm">{{ $i + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}"
                                        class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white text-sm">
                                            {{ $student->full_name }}
                                        </p>
                                        @if($student->admission_date)
                                        <p class="text-xs text-gray-400">Admitted {{ $student->admission_date->format('M Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-gray-600 dark:text-gray-400">
                                {{ $student->student_number }}
                            </td>
                            <td class="capitalize text-sm text-gray-600 dark:text-gray-400">
                                {{ $student->gender ?? '—' }}
                            </td>
                            <td>
                                @php
                                $sc = ['active'=>'badge-success','inactive'=>'badge-gray','suspended'=>'badge-danger','transferred'=>'badge-warning','graduated'=>'badge-purple'][$student->status] ?? 'badge-gray';
                                @endphp
                                <span class="badge {{ $sc }}">{{ ucfirst($student->status) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('students.show', $student) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
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
