@extends('layouts.app')

@section('title', $student->full_name)

@section('breadcrumbs')
<a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
<span class="text-gray-400 mx-1">/</span>
<a href="{{ route('students.index') }}" class="text-gray-500 hover:text-gray-700">Students</a>
<span class="text-gray-400 mx-1">/</span>
<span class="text-gray-900 font-medium">{{ $student->full_name }}</span>
@endsection

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="page-header">
    <div class="flex items-center gap-4">
        <a href="{{ route('students.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">{{ $student->full_name }}</h1>
            <p class="page-subtitle">{{ $student->student_number }} · {{ $student->currentClass?->name ?? 'Unassigned' }}</p>
        </div>
    </div>
    @can('edit students')
    <div class="flex gap-2">
        <a href="{{ route('students.edit', $student) }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Profile
        </a>
    </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

    {{-- Left: Profile card --}}
    <div class="space-y-4">
        <div class="card card-body text-center">
            <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-24 h-24 rounded-full mx-auto object-cover">
            <h3 class="font-bold text-gray-900 mt-3">{{ $student->full_name }}</h3>
            <p class="text-sm text-gray-500">{{ $student->student_number }}</p>
            @php
            $statusColors = [
                'active' => 'badge-success', 'inactive' => 'badge-gray',
                'graduated' => 'badge-purple', 'transferred' => 'badge-warning',
                'withdrawn' => 'badge-danger', 'suspended' => 'badge-danger',
            ];
            @endphp
            <span class="badge {{ $statusColors[$student->status] ?? 'badge-gray' }} mt-2">
                {{ ucfirst($student->status) }}
            </span>
        </div>

        {{-- Quick stats --}}
        <div class="card card-body space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Class</span>
                <span class="font-medium">{{ $student->currentClass?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Gender</span>
                <span class="font-medium">{{ ucfirst($student->gender ?? '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Age</span>
                <span class="font-medium">{{ $student->age ? $student->age . ' years' : '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Religion</span>
                <span class="font-medium">{{ $student->religion ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Blood Group</span>
                <span class="font-medium">{{ $student->blood_group ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Genotype</span>
                <span class="font-medium">{{ $student->genotype ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">House</span>
                <span class="font-medium">{{ $student->house ?? '—' }}</span>
            </div>
            @if($student->admission_date)
            <div class="flex justify-between">
                <span class="text-gray-500">Admitted</span>
                <span class="font-medium">{{ $student->admission_date->format('d M Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Tabs --}}
    <div class="lg:col-span-3" x-data="{ tab: 'profile' }">
        {{-- Tab navigation --}}
        <div class="flex border-b border-gray-200 mb-4 overflow-x-auto">
            @foreach([
                ['profile', 'Profile'],
                ['guardians', 'Guardians (' . $student->guardians->count() . ')'],
                ['attendance', 'Attendance'],
                ['academic', 'Academic'],
                ['fees', 'Fees'],
            ] as [$id, $label])
            <button @click="tab = '{{ $id }}'"
                :class="tab === '{{ $id }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="flex-shrink-0 px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- PROFILE TAB --}}
        <div x-show="tab === 'profile'" class="space-y-4">
            <div class="card card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Contact Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div>
                        <span class="text-gray-500">Phone</span>
                        <p class="font-medium">{{ $student->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Email</span>
                        <p class="font-medium">{{ $student->email ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-gray-500">Address</span>
                        <p class="font-medium">{{ $student->address ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">City</span>
                        <p class="font-medium">{{ $student->city ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">State / Nationality</span>
                        <p class="font-medium">{{ implode(', ', array_filter([$student->state, $student->nationality])) ?: '—' }}</p>
                    </div>
                </div>
            </div>

            @if($student->medical_conditions || $student->allergies)
            <div class="card card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Medical Information</h3>
                <div class="space-y-2 text-sm">
                    @if($student->medical_conditions)
                    <div>
                        <span class="text-gray-500">Medical Conditions</span>
                        <p class="font-medium">{{ $student->medical_conditions }}</p>
                    </div>
                    @endif
                    @if($student->allergies)
                    <div>
                        <span class="text-gray-500">Allergies</span>
                        <p class="font-medium">{{ $student->allergies }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($student->previous_school)
            <div class="card card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Previous Education</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Previous School</span>
                        <p class="font-medium">{{ $student->previous_school }}</p>
                    </div>
                    @if($student->previous_class)
                    <div>
                        <span class="text-gray-500">Previous Class</span>
                        <p class="font-medium">{{ $student->previous_class }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- GUARDIANS TAB --}}
        <div x-show="tab === 'guardians'" class="space-y-4">
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-500">{{ $student->guardians->count() }} guardian(s) linked</p>
                <div class="flex gap-2">
                    @can('create guardians')
                    <a href="{{ route('guardians.create', ['student_id' => $student->id]) }}" class="btn-secondary btn-sm">
                        + New Guardian
                    </a>
                    @endcan
                </div>
            </div>

            {{-- Existing linked guardians --}}
            @forelse($student->guardians as $guardian)
            <div class="card card-body" x-data="{ editingPivot: false }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ $guardian->photo_url }}" alt="{{ $guardian->full_name }}"
                            class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        <div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('guardians.show', $guardian) }}"
                                   class="font-semibold text-gray-900 dark:text-white hover:text-blue-600">
                                    {{ $guardian->full_name }}
                                </a>
                                @if($guardian->pivot->is_primary)
                                <span class="badge badge-success text-xs">Primary</span>
                                @endif
                                @if($guardian->pivot->is_emergency)
                                <span class="badge badge-danger text-xs">Emergency</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ $guardian->pivot->relationship ?? $guardian->relationship ?? 'Guardian' }}
                                @if($guardian->phone) · {{ $guardian->phone }} @endif
                            </p>
                            <div class="flex gap-1 mt-1">
                                @if($guardian->pivot->can_pickup)
                                <span class="text-xs bg-blue-50 dark:bg-blue-900/20 text-blue-600 px-1.5 py-0.5 rounded">Can pickup</span>
                                @endif
                                @if($guardian->pivot->receives_reports)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded">Gets reports</span>
                                @endif
                                @if($guardian->portal_access)
                                <span class="text-xs bg-purple-50 dark:bg-purple-900/20 text-purple-600 px-1.5 py-0.5 rounded">Portal</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @can('edit students')
                    <div class="flex gap-1 flex-shrink-0">
                        <button @click="editingPivot = !editingPivot"
                            class="text-xs text-gray-500 hover:text-blue-600 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                            Edit link
                        </button>
                        <form method="POST"
                              action="{{ route('students.guardians.detach', [$student, $guardian]) }}"
                              onsubmit="return confirm('Unlink {{ addslashes($guardian->first_name) }} from this student?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                                Unlink
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>

                {{-- Edit pivot inline --}}
                @can('edit students')
                <div x-show="editingPivot" x-collapse class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <form method="POST"
                          action="{{ route('students.guardians.update-pivot', [$student, $guardian]) }}">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label text-xs">Relationship</label>
                                <select name="relationship" class="form-select text-sm">
                                    <option value="">Select</option>
                                    @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Step-Father','Step-Mother','Sibling','Other'] as $rel)
                                    <option value="{{ $rel }}" @selected(($guardian->pivot->relationship ?? '') === $rel)>{{ $rel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5 pt-1">
                                @foreach([
                                    ['is_primary','Primary guardian'],
                                    ['is_emergency','Emergency contact'],
                                    ['can_pickup','Authorised to pick up'],
                                    ['receives_reports','Receives reports'],
                                    ['receives_invoices','Receives invoices'],
                                ] as [$field, $label])
                                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="hidden" name="{{ $field }}" value="0">
                                    <input type="checkbox" name="{{ $field }}" value="1"
                                        class="rounded border-gray-300 text-blue-600"
                                        @checked($guardian->pivot->$field ?? false)>
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="submit" class="btn-primary btn-sm text-xs">Save Changes</button>
                            <button type="button" @click="editingPivot = false" class="btn-secondary btn-sm text-xs">Cancel</button>
                        </div>
                    </form>
                </div>
                @endcan
            </div>
            @empty
            <div class="card card-body text-center py-10">
                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
                <p class="text-sm text-gray-500">No guardians linked yet.</p>
            </div>
            @endforelse

            {{-- Link existing guardian --}}
            @can('edit students')
            @php
            $linkedIds = $student->guardians->pluck('id')->toArray();
            $availableGuardians = \App\Models\Guardian::where('school_id', auth()->user()->school_id)
                ->whereNotIn('id', $linkedIds)->orderBy('first_name')->get();
            @endphp
            @if($availableGuardians->count() > 0)
            <div class="card p-4 border-dashed border-2 border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Link Existing Guardian</p>
                <form method="POST" action="{{ route('students.guardians.attach', $student) }}"
                      class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="flex-1 min-w-40">
                        <label class="form-label text-xs">Guardian</label>
                        <select name="guardian_id" class="form-select text-sm" required>
                            <option value="">Select guardian…</option>
                            @foreach($availableGuardians as $g)
                            <option value="{{ $g->id }}">{{ $g->full_name }} ({{ $g->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="form-label text-xs">Relationship</label>
                        <select name="relationship" class="form-select text-sm">
                            <option value="">Select</option>
                            @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Other'] as $rel)
                            <option value="{{ $rel }}">{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400 pb-1">
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300 text-blue-600">
                            Primary
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="hidden" name="can_pickup" value="0">
                            <input type="checkbox" name="can_pickup" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Can pickup
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="hidden" name="receives_reports" value="0">
                            <input type="checkbox" name="receives_reports" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Gets reports
                        </label>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Link Guardian</button>
                </form>
            </div>
            @endif
            @endcan
        </div>

        {{-- ATTENDANCE TAB --}}
        <div x-show="tab === 'attendance'" class="card card-body">
            <p class="text-sm text-gray-500">Attendance history will be displayed here.</p>
        </div>

        {{-- ACADEMIC TAB --}}
        <div x-show="tab === 'academic'" class="space-y-4">
            @forelse($student->enrolments->sortByDesc('enrolled_date') as $enrolment)
            <div class="card card-body flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium">{{ $enrolment->class?->name }}</p>
                    <p class="text-gray-500">Academic Year: {{ $enrolment->academicYear?->name }}</p>
                </div>
                <span class="badge {{ $enrolment->status === 'active' ? 'badge-success' : 'badge-gray' }}">{{ ucfirst($enrolment->status) }}</span>
            </div>
            @empty
            <div class="card card-body text-center py-10 text-gray-400">
                <p class="text-sm">No enrolment history</p>
            </div>
            @endforelse
        </div>

        {{-- FEES TAB --}}
        <div x-show="tab === 'fees'" class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Invoice</th><th>Term</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($student->invoices->sortByDesc('issue_date') as $invoice)
                        <tr>
                            <td class="font-medium">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->term?->name }}</td>
                            <td>₦{{ number_format($invoice->total_amount, 0) }}</td>
                            <td class="text-green-600">₦{{ number_format($invoice->amount_paid, 0) }}</td>
                            <td class="{{ $invoice->balance > 0 ? 'text-red-600' : 'text-gray-500' }}">₦{{ number_format($invoice->balance, 0) }}</td>
                            <td><span class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'partial' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($invoice->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-8 text-gray-400">No invoices</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</div>
@endsection
