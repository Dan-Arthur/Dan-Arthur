@extends('layouts.app')

@section('title', 'Application ' . $admission->application_number)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('admissions.index') }}" class="hover:text-blue-600">Admissions</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $admission->application_number }}</span>
        </div>
        <div class="flex items-center gap-3">
            <h1 class="page-title">{{ $admission->full_name }}</h1>
            @php
            $badgeClass = match($admission->status) {
                'draft'         => 'badge-gray',
                'submitted'     => 'badge-info',
                'under_review'  => 'badge-warning',
                'interview','entrance_exam' => 'badge-purple',
                'accepted','enrolled' => 'badge-success',
                'rejected'      => 'badge-danger',
                'waitlisted'    => 'badge-warning',
                default         => 'badge-gray',
            };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $admission->status_label }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @can('edit admissions')
        <a href="{{ route('admissions.edit', $admission) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete admissions')
        @if(!in_array($admission->status, ['enrolled']))
        <form method="POST" action="{{ route('admissions.destroy', $admission) }}"
              onsubmit="return confirm('Delete application #{{ $admission->application_number }}? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
        @endif
        @endcan
    </div>
</div>

{{-- Pipeline Progress Bar --}}
@php
$pipeline = ['draft','submitted','under_review','interview','accepted','enrolled'];
$currentIdx = array_search($admission->status, $pipeline);
if ($admission->status === 'entrance_exam') $currentIdx = array_search('interview', $pipeline);
if ($admission->status === 'rejected' || $admission->status === 'waitlisted') $currentIdx = -1;
@endphp

@if(!in_array($admission->status, ['rejected','waitlisted']))
<div class="card p-4 mb-6">
    <div class="flex items-center gap-0">
        @php
        $steps = [
            ['key'=>'draft','label'=>'Draft'],
            ['key'=>'submitted','label'=>'Submitted'],
            ['key'=>'under_review','label'=>'Review'],
            ['key'=>'interview','label'=>'Interview'],
            ['key'=>'accepted','label'=>'Accepted'],
            ['key'=>'enrolled','label'=>'Enrolled'],
        ];
        @endphp
        @foreach($steps as $i => $step)
        @php
        $stepIdx = $i;
        $isActive = $stepIdx === $currentIdx;
        $isPast   = $stepIdx < $currentIdx;
        $isFuture = $stepIdx > $currentIdx;
        @endphp
        <div class="flex items-center {{ $i < count($steps)-1 ? 'flex-1' : '' }}">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2
                    {{ $isPast  ? 'bg-blue-600 border-blue-600 text-white' : '' }}
                    {{ $isActive? 'bg-blue-600 border-blue-600 text-white ring-4 ring-blue-100' : '' }}
                    {{ $isFuture? 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400' : '' }}">
                    @if($isPast)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    {{ $i + 1 }}
                    @endif
                </div>
                <span class="text-xs mt-1 font-medium
                    {{ $isActive || $isPast ? 'text-blue-600' : 'text-gray-400 dark:text-gray-500' }}">
                    {{ $step['label'] }}
                </span>
            </div>
            @if($i < count($steps)-1)
            <div class="flex-1 h-0.5 mx-2 mb-4
                {{ $isPast ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@else
<div class="card p-4 mb-6 border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20">
    <p class="text-red-700 dark:text-red-400 font-medium">
        This application was <strong>{{ $admission->status_label }}</strong>
        @if($admission->decision_date) on {{ $admission->decision_date->format('d M Y') }} @endif.
    </p>
    @if($admission->decision_notes)
    <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ $admission->decision_notes }}</p>
    @endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Applicant Details --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Applicant Details</h3>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-y-5 gap-x-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Full Name</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $admission->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Gender</p>
                    <p class="mt-1 capitalize text-gray-900 dark:text-white">{{ $admission->gender ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Date of Birth</p>
                    <p class="mt-1 text-gray-900 dark:text-white">
                        {{ $admission->date_of_birth?->format('d M Y') ?? '—' }}
                        @if($admission->age) <span class="text-gray-400">(age {{ $admission->age }})</span> @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Nationality</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $admission->nationality ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Religion</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $admission->religion ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Phone</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $admission->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Email</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $admission->email ?? '—' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Address</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $admission->address ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Previous School</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $admission->previous_school ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Guardian Info --}}
        @if($admission->guardian_info)
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Guardian / Parent</h3>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-y-5 gap-x-4">
                @php $guardian = $admission->guardian_info; @endphp
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Name</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $guardian['name'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Relationship</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $guardian['relation'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Phone</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $guardian['phone'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Email</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $guardian['email'] ?? '—' }}</p>
                </div>
                @if(!empty($guardian['address']))
                <div class="col-span-2">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Address</p>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $guardian['address'] }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($admission->notes || $admission->decision_notes)
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Notes</h3>
            </div>
            <div class="p-6 space-y-4">
                @if($admission->notes)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Application Notes</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $admission->notes }}</p>
                </div>
                @endif
                @if($admission->decision_notes)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Decision Notes</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $admission->decision_notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="space-y-6">

        {{-- Application Info --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Application Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Number</span>
                    <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $admission->application_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Academic Year</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->academicYear?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Class Applied For</span>
                    <span class="text-gray-900 dark:text-white">
                        {{ $admission->appliedClass?->name ?? $admission->applying_for_class ?? '—' }}
                    </span>
                </div>
                @if($admission->campus)
                <div class="flex justify-between">
                    <span class="text-gray-500">Campus</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->campus->name }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Applied On</span>
                    <span class="text-gray-900 dark:text-white">
                        {{ $admission->application_date?->format('d M Y') ?? '—' }}
                    </span>
                </div>
                @if($admission->interview_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Interview</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->interview_date->format('d M Y') }}</span>
                </div>
                @endif
                @if($admission->exam_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Entrance Exam</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->exam_date->format('d M Y') }}</span>
                </div>
                @endif
                @if($admission->decision_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Decision Date</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->decision_date->format('d M Y') }}</span>
                </div>
                @endif
                @if($admission->reviewer)
                <div class="flex justify-between">
                    <span class="text-gray-500">Reviewed By</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->reviewer->name }}</span>
                </div>
                @endif
                @if($admission->decisionMaker)
                <div class="flex justify-between">
                    <span class="text-gray-500">Decision By</span>
                    <span class="text-gray-900 dark:text-white">{{ $admission->decisionMaker->name }}</span>
                </div>
                @endif
            </div>

            @if($admission->student)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('students.show', $admission->student) }}"
                   class="btn-primary w-full justify-center text-sm">
                    View Student Record →
                </a>
            </div>
            @endif
        </div>

        {{-- Status Workflow Actions --}}
        @can('edit admissions')
        @php
        $nextStatuses = collect(array_keys(\App\Models\Admission::STATUSES))->filter(
            fn($s) => $admission->canAdvanceTo($s)
        )->values();
        @endphp

        @if($nextStatuses->count() > 0 && $admission->status !== 'enrolled')
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Update Status</h3>
            <form method="POST" action="{{ route('admissions.update-status', $admission) }}">
                @csrf @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label class="form-label">Move To</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select next status…</option>
                            @foreach($nextStatuses as $s)
                            <option value="{{ $s }}">{{ \App\Models\Admission::STATUSES[$s]['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-data="{}" x-show="true">
                        <label class="form-label">Interview Date (if scheduling)</label>
                        <input type="date" name="interview_date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Exam Date (if scheduling)</label>
                        <input type="date" name="exam_date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Decision Notes</label>
                        <textarea name="decision_notes" rows="2"
                            class="form-input resize-none text-sm"
                            placeholder="Reason for decision, feedback…"></textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Enrol to Student --}}
        @if($admission->status === 'accepted' && !$admission->student_id)
        <div class="card p-5 border-2 border-green-200 dark:border-green-700">
            <h3 class="text-sm font-semibold text-green-700 dark:text-green-400 mb-3">
                Enrol as Student
            </h3>
            <p class="text-xs text-gray-500 mb-4">
                Create a student record from this accepted application.
            </p>
            <form method="POST" action="{{ route('admissions.enrol', $admission) }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="form-label">Assign to Class <span class="text-red-500">*</span></label>
                        <select name="current_class_id" class="form-select" required>
                            <option value="">Select class</option>
                            @foreach(\App\Models\SchoolClass::where('school_id', auth()->user()->school_id)->where('is_active', true)->orderBy('level')->orderBy('name')->get() as $class)
                            <option value="{{ $class->id }}"
                                @selected($admission->applied_class_id == $class->id)>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Admission Date <span class="text-red-500">*</span></label>
                        <input type="date" name="admission_date" value="{{ date('Y-m-d') }}"
                            class="form-input" required>
                    </div>
                    <button type="submit" class="btn-success w-full justify-center"
                        onclick="return confirm('Enrol {{ $admission->full_name }} as a student? This will create a student record.')">
                        Confirm Enrolment
                    </button>
                </div>
            </form>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
