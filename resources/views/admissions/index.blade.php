@extends('layouts.app')

@section('title', 'Admissions')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Admissions</h1>
        <p class="page-subtitle">Manage application pipeline and enrolment decisions</p>
    </div>
    @can('create admissions')
    <a href="{{ route('admissions.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Application
    </a>
    @endcan
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @php
    $kpis = [
        ['label' => 'Total',       'key' => 'total',        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'blue'],
        ['label' => 'Submitted',   'key' => 'submitted',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'indigo'],
        ['label' => 'Under Review','key' => 'under_review', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'color' => 'yellow'],
        ['label' => 'Accepted',    'key' => 'accepted',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'green'],
        ['label' => 'Enrolled',    'key' => 'enrolled',     'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'teal'],
        ['label' => 'Rejected',    'key' => 'rejected',     'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'red'],
    ];
    $colorMap = [
        'blue'   => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600',
        'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600',
        'yellow' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600',
        'green'  => 'bg-green-50 dark:bg-green-900/20 text-green-600',
        'teal'   => 'bg-teal-50 dark:bg-teal-900/20 text-teal-600',
        'red'    => 'bg-red-50 dark:bg-red-900/20 text-red-600',
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg {{ $colorMap[$kpi['color']] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats[$kpi['key']]) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $kpi['label'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('admissions.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input" placeholder="Name, app number, email…">
        </div>
        <div class="w-40">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statusOptions as $key => $info)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-44">
            <label class="form-label">Academic Year</label>
            <select name="academic_year_id" class="form-select">
                <option value="">All Years</option>
                @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label class="form-label">Class Applied For</label>
            <select name="class_id" class="form-select">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Any</option>
                <option value="male" @selected(request('gender') === 'male')>Male</option>
                <option value="female" @selected(request('gender') === 'female')>Female</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','status','academic_year_id','class_id','gender']))
        <a href="{{ route('admissions.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Application #</th>
                    <th>Applicant</th>
                    <th>Gender</th>
                    <th>Applying For</th>
                    <th>Academic Year</th>
                    <th>Applied</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admissions as $admission)
                @php
                $badgeClass = match($admission->status) {
                    'draft'         => 'badge-gray',
                    'submitted'     => 'badge-info',
                    'under_review'  => 'badge-warning',
                    'interview'     => 'badge-purple',
                    'entrance_exam' => 'badge-purple',
                    'accepted'      => 'badge-success',
                    'rejected'      => 'badge-danger',
                    'waitlisted'    => 'badge badge-warning',
                    'enrolled'      => 'badge-success',
                    default         => 'badge-gray',
                };
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('admissions.show', $admission) }}"
                           class="font-mono text-sm font-medium text-blue-600 hover:text-blue-800">
                            {{ $admission->application_number }}
                        </a>
                    </td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0
                                {{ $admission->gender === 'female' ? 'bg-pink-400' : 'bg-blue-400' }}">
                                {{ strtoupper(substr($admission->first_name, 0, 1) . substr($admission->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $admission->full_name }}</p>
                                @if($admission->email)
                                <p class="text-xs text-gray-500">{{ $admission->email }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="capitalize">{{ $admission->gender ?? '—' }}</td>
                    <td>{{ $admission->appliedClass?->name ?? $admission->applying_for_class ?? '—' }}</td>
                    <td>{{ $admission->academicYear?->name ?? '—' }}</td>
                    <td class="text-sm text-gray-500">
                        {{ $admission->application_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $admission->status_label }}</span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admissions.show', $admission) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                            @can('edit admissions')
                            <a href="{{ route('admissions.edit', $admission) }}"
                               class="text-gray-500 hover:text-gray-700 text-sm">Edit</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="font-medium">No applications found</p>
                        <p class="text-sm mt-1">
                            @can('create admissions')
                            <a href="{{ route('admissions.create') }}" class="text-blue-600">Create the first application</a>
                            @else
                            Adjust filters or check back later.
                            @endcan
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($admissions->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $admissions->links() }}
    </div>
    @endif
</div>
@endsection
