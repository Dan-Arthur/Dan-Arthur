@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">School configuration, academic years, campuses and system preferences</p>
    </div>
</div>

{{-- Tab navigation --}}
<div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
    @foreach([
        ['key'=>'school',   'label'=>'School Profile'],
        ['key'=>'years',    'label'=>'Academic Years'],
        ['key'=>'campuses', 'label'=>'Campuses'],
        ['key'=>'system',   'label'=>'System Settings'],
    ] as $t)
    <a href="{{ route('settings.index', ['tab' => $t['key']]) }}"
        class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
            {{ $tab === $t['key']
                ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
        {{ $t['label'] }}
    </a>
    @endforeach
</div>

{{-- ============================================================
     SCHOOL PROFILE TAB
     ============================================================ --}}
@if($tab === 'school')

<form method="POST" action="{{ route('settings.school.update') }}"
    enctype="multipart/form-data" class="space-y-6">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        {{-- Basic info --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Basic Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">School Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $school->name) }}"
                        class="form-input @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">School Code</label>
                    <input type="text" name="code" value="{{ old('code', $school->code) }}"
                        class="form-input" placeholder="e.g. GFA">
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">— Select type —</option>
                        @foreach(['private'=>'Private','public'=>'Public','faith-based'=>'Faith-Based','international'=>'International'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('type', $school->type) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Motto</label>
                    <input type="text" name="motto" value="{{ old('motto', $school->motto) }}"
                        class="form-input" placeholder="School motto or tagline">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Contact & Address
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" value="{{ old('address', $school->address) }}"
                        class="form-input" placeholder="Street address">
                </div>
                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city', $school->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">State / Region</label>
                    <input type="text" name="state" value="{{ old('state', $school->state) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Country</label>
                    <input type="text" name="country" value="{{ old('country', $school->country) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $school->postal_code) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $school->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $school->email) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" value="{{ old('website', $school->website) }}"
                        class="form-input" placeholder="https://…">
                </div>
            </div>
        </div>

        {{-- Academic structure --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Academic Structure
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Structure Type</label>
                    <select name="academic_structure" class="form-select">
                        @foreach(['semester'=>'Semester','trimester'=>'Trimester','term'=>'Term','quarter'=>'Quarter'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('academic_structure', $school->academic_structure) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Periods Per Year</label>
                    <select name="terms_per_year" class="form-select">
                        @foreach(range(1,6) as $n)
                        <option value="{{ $n }}" @selected(old('terms_per_year', $school->terms_per_year) == $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

    </div>

    {{-- Right column --}}
    <div class="space-y-6">

        {{-- Logo --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                School Logo
            </h3>
            @if($school->logo_url)
            <div class="mb-3 flex items-center gap-3">
                <img src="{{ $school->logo_url }}" alt="Logo" class="h-16 w-16 object-contain rounded border border-gray-200 dark:border-gray-700 p-1">
                <p class="text-xs text-gray-400">Current logo</p>
            </div>
            @endif
            <label class="form-label">Upload New Logo</label>
            <input type="file" name="logo" accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300">
            <p class="text-xs text-gray-400 mt-1.5">PNG or JPG, max 2 MB.</p>
            @error('logo')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Currency --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Currency
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="form-label">Currency Code</label>
                    <input type="text" name="currency_code"
                        value="{{ old('currency_code', $school->currency_code) }}"
                        class="form-input" placeholder="NGN, USD, GBP…" maxlength="10">
                </div>
                <div>
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" name="currency_symbol"
                        value="{{ old('currency_symbol', $school->currency_symbol) }}"
                        class="form-input" placeholder="₦, $, £…" maxlength="10">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3">Save School Profile</button>

    </div>
</div>
</form>

{{-- ============================================================
     ACADEMIC YEARS TAB
     ============================================================ --}}
@elseif($tab === 'years')

{{-- Add Year form --}}
<div class="card p-5 mb-6" x-data="{ open: false }">
    <button @click="open = !open"
        class="w-full flex items-center justify-between text-sm font-semibold text-gray-700 dark:text-gray-300">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Academic Year
        </span>
        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="mt-4">
        <form method="POST" action="{{ route('settings.years.store') }}">
        @csrf
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="form-label text-xs">Year Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="form-input text-sm" placeholder="e.g. 2025/2026" required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label text-xs">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-input text-sm" required>
            </div>
            <div>
                <label class="form-label text-xs">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input text-sm" required>
            </div>
            <div>
                <label class="form-label text-xs">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-select text-sm" required>
                    <option value="upcoming" @selected(old('status','upcoming')==='upcoming')>Upcoming</option>
                    <option value="active"   @selected(old('status')==='active')>Active</option>
                    <option value="completed"@selected(old('status')==='completed')>Completed</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-primary mt-3 text-sm">Create Year</button>
        </form>
    </div>
</div>

{{-- Year cards --}}
@forelse($academicYears as $year)
<div class="card mb-4 overflow-hidden" x-data="{ termsOpen: {{ $year->is_current ? 'true' : 'false' }}, addTerm: false, editYear: false }">

    {{-- Year header --}}
    <div class="px-6 py-4 flex items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-3 flex-wrap">
            <h3 class="font-bold text-gray-900 dark:text-white">{{ $year->name }}</h3>
            @if($year->is_current)
            <span class="badge badge-success text-xs">Current Year</span>
            @endif
            @php
            $ySc = ['upcoming'=>'badge-gray','active'=>'badge-info','completed'=>'badge-purple'][$year->status] ?? 'badge-gray';
            @endphp
            <span class="badge {{ $ySc }} text-xs">{{ ucfirst($year->status) }}</span>
            <span class="text-xs text-gray-400">
                {{ $year->start_date?->format('d M Y') }} — {{ $year->end_date?->format('d M Y') }}
            </span>
            <span class="text-xs text-gray-400">{{ $year->terms->count() }} term(s)</span>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(!$year->is_current)
            <form method="POST" action="{{ route('settings.years.set-current', $year) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-secondary text-xs py-1 px-2">Set Current</button>
            </form>
            @endif
            <button @click="editYear = !editYear" class="icon-btn" title="Edit year">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            <button @click="termsOpen = !termsOpen" class="icon-btn" title="Toggle terms">
                <svg :class="termsOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            @if(!$year->is_current)
            <form method="POST" action="{{ route('settings.years.destroy', $year) }}"
                onsubmit="return confirm('Delete academic year {{ addslashes($year->name) }}? This will also delete all its terms.')">
                @csrf @method('DELETE')
                <button type="submit" class="icon-btn text-red-500 hover:text-red-700" title="Delete year">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Edit year form --}}
    <div x-show="editYear" x-collapse class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700">
        <form method="POST" action="{{ route('settings.years.update', $year) }}" class="px-6 py-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="form-label text-xs">Year Name</label>
                <input type="text" name="name" value="{{ old('name', $year->name) }}" class="form-input text-sm" required>
            </div>
            <div>
                <label class="form-label text-xs">Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', $year->start_date?->toDateString()) }}" class="form-input text-sm" required>
            </div>
            <div>
                <label class="form-label text-xs">End Date</label>
                <input type="date" name="end_date" value="{{ old('end_date', $year->end_date?->toDateString()) }}" class="form-input text-sm" required>
            </div>
            <div>
                <label class="form-label text-xs">Status</label>
                <select name="status" class="form-select text-sm">
                    <option value="upcoming"  @selected($year->status==='upcoming')>Upcoming</option>
                    <option value="active"    @selected($year->status==='active')>Active</option>
                    <option value="completed" @selected($year->status==='completed')>Completed</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn-primary text-sm py-1.5 px-3">Save Year</button>
            <button type="button" @click="editYear = false" class="btn-secondary text-sm py-1.5 px-3">Cancel</button>
        </div>
        </form>
    </div>

    {{-- Terms section --}}
    <div x-show="termsOpen" x-collapse>
        <div class="px-6 py-4">

            {{-- Terms table --}}
            @if($year->terms->isEmpty())
            <p class="text-sm text-gray-400 italic mb-3">No terms yet. Add one below.</p>
            @else
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Term</th>
                            <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Dates</th>
                            <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($year->terms as $term)
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-400">{{ $term->sequence }}</td>
                            <td class="py-2 pr-4 font-medium text-gray-900 dark:text-white">
                                {{ $term->name }}
                                @if($term->is_current)
                                <span class="badge badge-success text-xs ml-1">Current</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-gray-500 text-xs">
                                {{ $term->start_date?->format('d M Y') }} — {{ $term->end_date?->format('d M Y') }}
                                @if($term->result_release_date)
                                    <br><span class="text-gray-400">Results: {{ $term->result_release_date->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                @php $tSc = ['upcoming'=>'badge-gray','active'=>'badge-info','completed'=>'badge-purple'][$term->status] ?? 'badge-gray'; @endphp
                                <span class="badge {{ $tSc }} text-xs">{{ ucfirst($term->status) }}</span>
                            </td>
                            <td class="py-2" x-data="{ editTerm: false }">
                                <div class="flex items-center gap-1">
                                    <button @click="editTerm = !editTerm" class="text-xs text-gray-500 hover:text-blue-600 hover:underline">Edit</button>
                                    @if(!$term->is_current)
                                    <form method="POST" action="{{ route('settings.terms.set-current', $term) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs text-blue-600 hover:underline whitespace-nowrap ml-1">Set Current</button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('settings.terms.destroy', $term) }}"
                                        onsubmit="return confirm('Delete term {{ addslashes($term->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:underline ml-1"
                                            @if($term->is_current) disabled @endif>Delete</button>
                                    </form>
                                </div>
                                {{-- Inline edit form --}}
                                <div x-show="editTerm" x-collapse class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <form method="POST" action="{{ route('settings.terms.update', $term) }}">
                                        @csrf @method('PUT')
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <label class="form-label text-xs">Name</label>
                                                <input type="text" name="name" value="{{ $term->name }}" class="form-input text-xs py-1" required>
                                            </div>
                                            <div>
                                                <label class="form-label text-xs">Sequence</label>
                                                <input type="number" name="sequence" value="{{ $term->sequence }}" class="form-input text-xs py-1" min="1" required>
                                            </div>
                                            <div>
                                                <label class="form-label text-xs">Start Date</label>
                                                <input type="date" name="start_date" value="{{ $term->start_date?->toDateString() }}" class="form-input text-xs py-1" required>
                                            </div>
                                            <div>
                                                <label class="form-label text-xs">End Date</label>
                                                <input type="date" name="end_date" value="{{ $term->end_date?->toDateString() }}" class="form-input text-xs py-1" required>
                                            </div>
                                            <div>
                                                <label class="form-label text-xs">Results Out</label>
                                                <input type="date" name="result_release_date" value="{{ $term->result_release_date?->toDateString() }}" class="form-input text-xs py-1">
                                            </div>
                                            <div>
                                                <label class="form-label text-xs">Status</label>
                                                <select name="status" class="form-select text-xs py-1">
                                                    <option value="upcoming"  @selected($term->status==='upcoming')>Upcoming</option>
                                                    <option value="active"    @selected($term->status==='active')>Active</option>
                                                    <option value="completed" @selected($term->status==='completed')>Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="flex gap-1 mt-2">
                                            <button type="submit" class="btn-primary text-xs py-1 px-2">Save</button>
                                            <button type="button" @click="editTerm = false" class="btn-secondary text-xs py-1 px-2">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Add Term --}}
            <div x-data="{ addTerm: false }">
                <button @click="addTerm = !addTerm"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Term
                </button>
                <div x-show="addTerm" x-collapse class="mt-3">
                    <form method="POST" action="{{ route('settings.terms.store', $year) }}">
                    @csrf
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                        <div>
                            <label class="form-label text-xs">Sequence</label>
                            <input type="number" name="sequence" value="{{ $year->terms->max('sequence') + 1 }}"
                                min="1" class="form-input text-sm" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">Name</label>
                            <input type="text" name="name" class="form-input text-sm"
                                placeholder="First Term" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">Start</label>
                            <input type="date" name="start_date" class="form-input text-sm" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">End</label>
                            <input type="date" name="end_date" class="form-input text-sm" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">Results Out</label>
                            <input type="date" name="result_release_date" class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label text-xs">Status</label>
                            <select name="status" class="form-select text-sm">
                                <option value="upcoming">Upcoming</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary text-sm py-1.5 px-3 mt-2">Add Term</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@empty
<div class="card p-10 text-center text-gray-400 text-sm">
    No academic years configured. Add one above.
</div>
@endforelse

{{-- ============================================================
     CAMPUSES TAB
     ============================================================ --}}
@elseif($tab === 'campuses')

{{-- Add Campus form --}}
<div class="card p-5 mb-6" x-data="{ open: false }">
    <button @click="open = !open"
        class="w-full flex items-center justify-between text-sm font-semibold text-gray-700 dark:text-gray-300">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Campus
        </span>
        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="mt-4">
        <form method="POST" action="{{ route('settings.campuses.store') }}">
        @csrf
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div>
                <label class="form-label text-xs">Campus Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input text-sm" placeholder="e.g. Victoria Island Campus" required>
                @error('name')<p class="form-error text-xs">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label text-xs">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" class="form-input text-sm" placeholder="e.g. VI" required>
                @error('code')<p class="form-error text-xs">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label text-xs">City</label>
                <input type="text" name="city" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">Phone</label>
                <input type="tel" name="phone" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">Email</label>
                <input type="email" name="email" class="form-input text-sm">
            </div>
            <div class="flex items-center gap-4 pt-5">
                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded">
                    Active
                </label>
                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                    <input type="hidden" name="is_main_campus" value="0">
                    <input type="checkbox" name="is_main_campus" value="1" class="rounded">
                    Main Campus
                </label>
            </div>
        </div>
        <div>
            <label class="form-label text-xs mt-3">Address</label>
            <input type="text" name="address" class="form-input text-sm" placeholder="Full address">
        </div>
        <button type="submit" class="btn-primary mt-3 text-sm">Create Campus</button>
        </form>
    </div>
</div>

{{-- Campuses table --}}
<div class="card overflow-hidden">
    @if($campuses->isEmpty())
    <div class="p-10 text-center text-gray-400 text-sm">No campuses configured.</div>
    @else
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Campus</th>
                    <th>Code</th>
                    <th>Contact</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th class="w-28"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($campuses as $campus)
                <tr x-data="{ editing: false }">
                    <td>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ $campus->name }}
                                @if($campus->is_main_campus)
                                <span class="badge badge-info text-xs ml-1">Main</span>
                                @endif
                            </p>
                            @if($campus->address)
                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $campus->address }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="font-mono text-sm text-gray-500">{{ $campus->code }}</td>
                    <td class="text-sm text-gray-500">
                        @if($campus->phone)<div>{{ $campus->phone }}</div>@endif
                        @if($campus->email)<div class="text-xs">{{ $campus->email }}</div>@endif
                    </td>
                    <td class="text-sm text-gray-500">{{ number_format($campus->students_count) }}</td>
                    <td>
                        <span class="badge {{ $campus->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $campus->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center gap-1 justify-end">
                            <button @click="editing = !editing" class="icon-btn" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @if(!$campus->is_main_campus)
                            <form method="POST" action="{{ route('settings.campuses.destroy', $campus) }}"
                                onsubmit="return confirm('Delete campus {{ addslashes($campus->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                {{-- Inline edit row --}}
                <tr x-data="" x-show="$el.previousElementSibling.__x && $el.previousElementSibling.__x.$data.editing"
                    style="display:none" class="bg-gray-50 dark:bg-gray-800/50">
                    <td colspan="6" class="px-6 py-4">
                        <form method="POST" action="{{ route('settings.campuses.update', $campus) }}">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="form-label text-xs">Name</label>
                                <input type="text" name="name" value="{{ $campus->name }}" class="form-input text-sm" required>
                            </div>
                            <div>
                                <label class="form-label text-xs">Code</label>
                                <input type="text" name="code" value="{{ $campus->code }}" class="form-input text-sm" required>
                            </div>
                            <div>
                                <label class="form-label text-xs">City</label>
                                <input type="text" name="city" value="{{ $campus->city }}" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Phone</label>
                                <input type="tel" name="phone" value="{{ $campus->phone }}" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Email</label>
                                <input type="email" name="email" value="{{ $campus->email }}" class="form-input text-sm">
                            </div>
                            <div class="flex items-center gap-4 pt-5">
                                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="rounded" @checked($campus->is_active)>
                                    Active
                                </label>
                                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="hidden" name="is_main_campus" value="0">
                                    <input type="checkbox" name="is_main_campus" value="1" class="rounded" @checked($campus->is_main_campus)>
                                    Main Campus
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-xs mt-2">Address</label>
                            <input type="text" name="address" value="{{ $campus->address }}" class="form-input text-sm">
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="submit" class="btn-primary text-sm py-1.5 px-3">Save</button>
                            <button type="button" onclick="this.closest('tr').style.display='none';this.closest('tr').previousElementSibling.__x.$data.editing=false"
                                class="btn-secondary text-sm py-1.5 px-3">Cancel</button>
                        </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ============================================================
     SYSTEM SETTINGS TAB
     ============================================================ --}}
@elseif($tab === 'system')

<form method="POST" action="{{ route('settings.system.update') }}" class="space-y-6">
@csrf @method('PUT')

@php
$sv = fn(string $key, mixed $default = null) => $systemSettings[$key]?->value ?? $default;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Numbering --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
            Numbering & ID Prefixes
        </h3>
        <p class="text-xs text-gray-400 mb-5">
            Prefixes are prepended to generated IDs. Example: prefix <code>GFA</code> → <code>GFA20250001</code>.
        </p>
        <div class="space-y-4">
            <div>
                <label class="form-label">Admission Number Prefix</label>
                <input type="text" name="admission_number_prefix"
                    value="{{ old('admission_number_prefix', $sv('admission_number_prefix', 'APP')) }}"
                    class="form-input @error('admission_number_prefix') border-red-500 @enderror"
                    placeholder="APP" maxlength="20">
                <p class="text-xs text-gray-400 mt-1">Used for new application numbers.</p>
                @error('admission_number_prefix')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Student Number Prefix</label>
                <input type="text" name="student_number_prefix"
                    value="{{ old('student_number_prefix', $sv('student_number_prefix', 'STU')) }}"
                    class="form-input @error('student_number_prefix') border-red-500 @enderror"
                    placeholder="STU" maxlength="20">
                <p class="text-xs text-gray-400 mt-1">Used when enrolling admitted students.</p>
                @error('student_number_prefix')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Finance --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
            Finance
        </h3>
        <p class="text-xs text-gray-400 mb-5">Invoice and payment defaults.</p>
        <div>
            <label class="form-label">Invoice Due Days</label>
            <input type="number" name="invoice_due_days"
                value="{{ old('invoice_due_days', $sv('invoice_due_days', 30)) }}"
                class="form-input max-w-[120px] @error('invoice_due_days') border-red-500 @enderror"
                min="1" max="365">
            <p class="text-xs text-gray-400 mt-1">Days after invoice date before it is considered overdue.</p>
            @error('invoice_due_days')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Academic --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
            Results & Assessment
        </h3>
        <p class="text-xs text-gray-400 mb-5">Controls how results are reviewed and published.</p>
        <div class="space-y-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="result_approval_required" value="0">
                <input type="checkbox" name="result_approval_required" value="1"
                    @checked(filter_var($sv('result_approval_required', 'true'), FILTER_VALIDATE_BOOLEAN))
                    class="mt-0.5 rounded flex-shrink-0">
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Require result approval</p>
                    <p class="text-xs text-gray-400">Results must be approved by a principal before students can view them.</p>
                </div>
            </label>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="show_position_on_result" value="0">
                <input type="checkbox" name="show_position_on_result" value="1"
                    @checked(filter_var($sv('show_position_on_result', 'true'), FILTER_VALIDATE_BOOLEAN))
                    class="mt-0.5 rounded flex-shrink-0">
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Show class position on results</p>
                    <p class="text-xs text-gray-400">Students and guardians will see the student's rank in their class.</p>
                </div>
            </label>
        </div>
    </div>

    {{-- Attendance --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
            Attendance
        </h3>
        <p class="text-xs text-gray-400 mb-5">Time thresholds for attendance marking.</p>
        <div class="space-y-4">
            <div>
                <label class="form-label">School Start Time</label>
                <input type="time" name="attendance_start_time"
                    value="{{ old('attendance_start_time', $sv('attendance_start_time', '07:00')) }}"
                    class="form-input max-w-[140px] @error('attendance_start_time') border-red-500 @enderror">
                <p class="text-xs text-gray-400 mt-1">Arrival time at or before this is counted as on-time.</p>
                @error('attendance_start_time')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Late Arrival Threshold (minutes)</label>
                <input type="number" name="late_threshold_minutes"
                    value="{{ old('late_threshold_minutes', $sv('late_threshold_minutes', 15)) }}"
                    class="form-input max-w-[120px] @error('late_threshold_minutes') border-red-500 @enderror"
                    min="0" max="120">
                <p class="text-xs text-gray-400 mt-1">Minutes after start time before marking a student as late.</p>
                @error('late_threshold_minutes')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

</div>

<div>
    <button type="submit" class="btn-primary py-3 px-8">Save System Settings</button>
</div>

</form>
@endif

@endsection
