@extends('layouts.app')

@section('title', $guardian->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('guardians.index') }}" class="hover:text-blue-600">Guardians</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $guardian->full_name }}</span>
        </div>
        <h1 class="page-title">{{ $guardian->full_name }}</h1>
    </div>
    <div class="flex items-center gap-2">
        @can('edit guardians')
        <form method="POST" action="{{ route('guardians.toggle-portal', $guardian) }}">
            @csrf @method('PATCH')
            <button type="submit"
                class="{{ $guardian->portal_access ? 'btn-secondary' : 'btn-primary' }}">
                {{ $guardian->portal_access ? 'Disable Portal' : 'Enable Portal' }}
            </button>
        </form>
        <a href="{{ route('guardians.edit', $guardian) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete guardians')
        @if($guardian->students->count() === 0)
        <form method="POST" action="{{ route('guardians.destroy', $guardian) }}"
              onsubmit="return confirm('Delete {{ $guardian->full_name }}? This cannot be undone.')">
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
        {{-- Avatar + Status card --}}
        <div class="card p-6 text-center">
            <img src="{{ $guardian->photo_url }}" alt="{{ $guardian->full_name }}"
                class="w-24 h-24 rounded-full mx-auto object-cover">
            <h2 class="font-bold text-gray-900 dark:text-white mt-3 text-lg">{{ $guardian->full_name }}</h2>
            <p class="text-sm text-gray-500">{{ $guardian->relationship ?? 'Guardian' }}</p>
            <div class="flex items-center justify-center gap-2 mt-3">
                <span class="badge {{ $guardian->status === 'active' ? 'badge-success' : 'badge-gray' }}">
                    {{ ucfirst($guardian->status) }}
                </span>
                @if($guardian->portal_access)
                <span class="badge badge-purple">Portal</span>
                @endif
            </div>
        </div>

        {{-- Contact card --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Contact</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <div>
                        <a href="tel:{{ $guardian->phone }}" class="text-blue-600 hover:underline">{{ $guardian->phone }}</a>
                        @if($guardian->alt_phone)
                        <br><a href="tel:{{ $guardian->alt_phone }}" class="text-gray-500 text-xs">{{ $guardian->alt_phone }}</a>
                        @endif
                    </div>
                </div>
                @if($guardian->email)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <a href="mailto:{{ $guardian->email }}" class="text-blue-600 hover:underline">{{ $guardian->email }}</a>
                </div>
                @endif
                @if($guardian->address)
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ $guardian->address }}
                        @if($guardian->city || $guardian->state)
                        <br>{{ implode(', ', array_filter([$guardian->city, $guardian->state])) }}
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Professional --}}
        @if($guardian->occupation || $guardian->employer)
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Professional</h3>
            <div class="space-y-2 text-sm">
                @if($guardian->occupation)
                <div>
                    <p class="text-gray-500">Occupation</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guardian->occupation }}</p>
                </div>
                @endif
                @if($guardian->employer)
                <div>
                    <p class="text-gray-500">Employer</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guardian->employer }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ID --}}
        @if($guardian->national_id || $guardian->nationality)
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Identity</h3>
            <div class="space-y-2 text-sm">
                @if($guardian->nationality)
                <div>
                    <p class="text-gray-500">Nationality</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guardian->nationality }}</p>
                </div>
                @endif
                @if($guardian->national_id)
                <div>
                    <p class="text-gray-500">National ID</p>
                    <p class="font-mono font-medium text-gray-900 dark:text-white">{{ $guardian->national_id }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: Linked Students --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Students header --}}
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                Linked Students ({{ $guardian->students->count() }})
            </h3>
            @can('edit guardians')
            <a href="{{ route('students.index') }}" class="text-sm text-blue-600 hover:underline">
                Browse all students →
            </a>
            @endcan
        </div>

        @forelse($guardian->students as $student)
        <div class="card p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}"
                        class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                    <div>
                        <a href="{{ route('students.show', $student) }}"
                           class="font-semibold text-gray-900 dark:text-white hover:text-blue-600">
                            {{ $student->full_name }}
                        </a>
                        <p class="text-sm text-gray-500">
                            {{ $student->student_number }} · {{ $student->currentClass?->name ?? 'No class' }}
                        </p>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @if($student->pivot->relationship)
                            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">
                                {{ $student->pivot->relationship }}
                            </span>
                            @endif
                            @if($student->pivot->is_primary)
                            <span class="badge badge-success text-xs">Primary</span>
                            @endif
                            @if($student->pivot->is_emergency)
                            <span class="badge badge-danger text-xs">Emergency</span>
                            @endif
                            @if($student->pivot->can_pickup)
                            <span class="badge badge-info text-xs">Can Pickup</span>
                            @endif
                            @if($student->pivot->receives_reports)
                            <span class="badge badge-gray text-xs">Gets Reports</span>
                            @endif
                        </div>
                    </div>
                </div>
                @can('edit guardians')
                <div class="flex gap-2 flex-shrink-0">
                    <a href="{{ route('students.show', $student) }}" class="btn-secondary btn-sm text-xs">
                        View
                    </a>
                    <form method="POST"
                          action="{{ route('students.guardians.detach', [$student, $guardian]) }}"
                          onsubmit="return confirm('Unlink {{ $guardian->first_name }} from {{ $student->first_name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm text-xs">Unlink</button>
                    </form>
                </div>
                @endcan
            </div>
        </div>
        @empty
        <div class="card p-10 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <p class="text-gray-500 font-medium">No students linked</p>
            <p class="text-sm text-gray-400 mt-1">
                Link this guardian to a student from the student's profile page.
            </p>
        </div>
        @endforelse

        {{-- Portal Access Info --}}
        @if($guardian->portal_access && $guardian->user)
        <div class="card p-5 border-l-4 border-purple-400">
            <h4 class="font-medium text-gray-900 dark:text-white mb-1">Parent Portal Account</h4>
            <p class="text-sm text-gray-500">Linked to: <strong>{{ $guardian->user->email }}</strong></p>
        </div>
        @endif
    </div>

</div>
@endsection
