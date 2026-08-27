@extends('layouts.app')

@section('title', 'Feeding Program')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Feeding Program</h1>
        @if ($currentYear)
            <p class="text-sm text-muted mt-1">{{ $currentYear->name }}</p>
        @endif
    </div>
    <div class="flex gap-3">
        @can('record feeding')
        <a href="{{ route('feeding.records') }}" class="btn btn-primary">Record Today's Feeding</a>
        @endcan
        @can('manage feeding')
        <a href="{{ route('feeding.enrollment') }}" class="btn btn-ghost">Manage Enrollment</a>
        @endcan
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card text-center">
        <p class="text-3xl font-bold text-primary">{{ $enrolledCount }}</p>
        <p class="text-sm text-muted mt-1">Enrolled This Year</p>
    </div>
    <div class="card text-center">
        @if ($recordedToday)
            <p class="text-3xl font-bold text-success">{{ $fedToday }}</p>
            <p class="text-sm text-muted mt-1">Fed Today</p>
        @else
            <p class="text-3xl font-bold text-muted">—</p>
            <p class="text-sm text-muted mt-1">Not Yet Recorded</p>
        @endif
    </div>
    <div class="card text-center">
        @if ($recordedToday)
            <p class="text-3xl font-bold {{ $absentToday > 0 ? 'text-danger' : 'text-success' }}">{{ $absentToday }}</p>
            <p class="text-sm text-muted mt-1">Absent Today</p>
        @else
            <p class="text-3xl font-bold text-muted">—</p>
            <p class="text-sm text-muted mt-1">Absent Today</p>
        @endif
    </div>
    <div class="card text-center">
        @if ($recordedToday)
            <span class="badge badge-success text-sm">Recorded</span>
        @else
            <span class="badge badge-warning text-sm">Pending</span>
        @endif
        <p class="text-sm text-muted mt-1">Today's Status</p>
    </div>
</div>

{{-- Recent Activity --}}
<div class="card">
    <h2 class="card-title mb-4">Recent Records</h2>
    @if ($recentDates->isEmpty())
        <p class="text-muted text-sm">No feeding records yet.</p>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th class="text-right">Fed</th>
                        <th class="text-right">Total Enrolled</th>
                        <th class="text-right">Absent</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentDates as $row)
                    @php $d = \Carbon\Carbon::parse($row->record_date); @endphp
                    <tr>
                        <td class="font-mono text-sm">{{ $d->format('d M Y') }}</td>
                        <td class="text-muted text-sm">{{ $d->format('l') }}</td>
                        <td class="text-right font-semibold text-success">{{ $row->fed_count }}</td>
                        <td class="text-right text-muted">{{ $row->total }}</td>
                        <td class="text-right {{ ($row->total - $row->fed_count) > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $row->total - $row->fed_count }}
                        </td>
                        <td class="text-right">
                            @can('record feeding')
                            <a href="{{ route('feeding.records', ['date' => $row->record_date]) }}" class="btn btn-xs btn-ghost">View / Edit</a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
