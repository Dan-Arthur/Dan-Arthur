@extends('layouts.app')

@section('title', 'Timetable Periods')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('timetables.index') }}" class="hover:text-blue-600">Timetable</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Manage Periods</span>
        </div>
        <h1 class="page-title">Timetable Periods</h1>
    </div>
    <a href="{{ route('timetables.index') }}" class="btn-secondary">← Back to Timetable</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Add period form --}}
    <div class="lg:col-span-2">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Add Period</h3>
            <form method="POST" action="{{ route('timetables.periods.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label text-xs">Period Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-input text-sm @error('name') border-red-500 @enderror"
                        placeholder="e.g. Period 1, Break, Assembly" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs">Start Time <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}"
                            class="form-input text-sm @error('start_time') border-red-500 @enderror" required>
                        @error('start_time')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label text-xs">End Time <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}"
                            class="form-input text-sm @error('end_time') border-red-500 @enderror" required>
                        @error('end_time')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="form-label text-xs">Sort Order <span class="text-red-500">*</span></label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $periods->count() + 1) }}"
                        class="form-input text-sm" min="1" required>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_break" value="0">
                        <input type="checkbox" name="is_break" value="1"
                            @checked(old('is_break')) class="rounded">
                        <span class="text-sm text-gray-700 dark:text-gray-300">This is a break period</span>
                    </label>
                </div>
                <button type="submit" class="btn-primary text-sm w-full">Add Period</button>
            </form>
        </div>
    </div>

    {{-- Periods table --}}
    <div class="lg:col-span-3">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Periods ({{ $periods->count() }})
                </h3>
            </div>
            @if($periods->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">
                No periods defined yet. Add your first period.
            </div>
            @else
            <div class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach($periods as $period)
                <div x-data="{ editing: false }">
                    {{-- Display row --}}
                    <div x-show="!editing" class="flex items-center gap-4 px-6 py-3">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full
                            {{ $period->is_break ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}
                            text-xs font-bold">
                            {{ $period->sort_order }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-sm text-gray-900 dark:text-white">{{ $period->name }}</span>
                                @if($period->is_break)
                                <span class="badge badge-warning text-xs">Break</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ substr($period->start_time,0,5) }} – {{ substr($period->end_time,0,5) }}
                                <span class="ml-1">({{ $period->duration }})</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="editing = true" class="text-xs text-blue-600 hover:underline">Edit</button>
                            <form method="POST" action="{{ route('timetables.periods.destroy', $period) }}"
                                onsubmit="return confirm('Delete period {{ addslashes($period->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>

                    {{-- Edit row --}}
                    <div x-show="editing" style="display:none" class="px-6 py-3 bg-gray-50 dark:bg-gray-800/40">
                        <form method="POST" action="{{ route('timetables.periods.update', $period) }}">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                <div class="sm:col-span-2">
                                    <label class="form-label text-xs">Name</label>
                                    <input type="text" name="name" value="{{ $period->name }}"
                                        class="form-input text-sm" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Start</label>
                                    <input type="time" name="start_time" value="{{ substr($period->start_time,0,5) }}"
                                        class="form-input text-sm" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs">End</label>
                                    <input type="time" name="end_time" value="{{ substr($period->end_time,0,5) }}"
                                        class="form-input text-sm" required>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div>
                                    <label class="form-label text-xs">Order</label>
                                    <input type="number" name="sort_order" value="{{ $period->sort_order }}"
                                        class="form-input text-sm w-20" min="1" required>
                                </div>
                                <div class="flex items-end pb-1">
                                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                        <input type="hidden" name="is_break" value="0">
                                        <input type="checkbox" name="is_break" value="1"
                                            @checked($period->is_break) class="rounded">
                                        Break
                                    </label>
                                </div>
                                <div class="flex gap-2 items-end pb-0.5">
                                    <button type="submit" class="btn-primary text-sm py-1.5 px-3">Save</button>
                                    <button type="button" @click="editing = false" class="btn-secondary text-sm py-1.5 px-3">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
