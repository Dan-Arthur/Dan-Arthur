@extends('layouts.app')
@section('title', 'Edit — ' . $gradingScale->name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Grading Scale</h1>
        <p class="page-subtitle">{{ $gradingScale->name }}</p>
    </div>
    <a href="{{ route('grading-scales.index') }}" class="btn btn-ghost">Cancel</a>
</div>

@php
$existingBands = $gradingScale->bands->map(fn($b) => [
    'grade'     => $b->grade,
    'min_score' => $b->min_score,
    'max_score' => $b->max_score,
    'remark'    => $b->remark ?? '',
    'gpa_point' => $b->gpa_point,
])->values()->toArray();
@endphp

<form method="POST" action="{{ route('grading-scales.update', $gradingScale) }}"
      x-data="gradingForm(@json($existingBands))" class="max-w-3xl space-y-5">
    @csrf @method('PUT')

    <div class="card p-5 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Scale Name <span class="required">*</span></label>
                <input type="text" name="name" value="{{ old('name', $gradingScale->name) }}"
                    class="form-input" required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group flex items-end pb-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="rounded"
                        {{ old('is_default', $gradingScale->is_default) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                        Set as default scale
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Grade Bands</h3>
            <button type="button" @click="addBand()" class="btn btn-xs btn-ghost">+ Add Band</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">Grade</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Min Score</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Max Score</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-24">GPA Point</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    <template x-for="(band, i) in bands" :key="i">
                        <tr>
                            <td class="px-4 py-2">
                                <input type="text" :name="'bands['+i+'][grade]'" x-model="band.grade"
                                    class="form-input text-sm font-bold w-16 text-center" required>
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" :name="'bands['+i+'][min_score]'" x-model.number="band.min_score"
                                    class="form-input text-sm font-mono w-24" min="0" max="100" step="0.01" required>
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" :name="'bands['+i+'][max_score]'" x-model.number="band.max_score"
                                    class="form-input text-sm font-mono w-24" min="0" max="100" step="0.01" required>
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" :name="'bands['+i+'][remark]'" x-model="band.remark"
                                    class="form-input text-sm w-full">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" :name="'bands['+i+'][gpa_point]'" x-model.number="band.gpa_point"
                                    class="form-input text-sm font-mono w-16 text-center" min="0" max="10">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="bands.splice(i, 1)"
                                    class="text-gray-300 hover:text-red-500 text-lg leading-none transition-colors">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
            Saving replaces all existing bands with the ones above.
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('grading-scales.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
function gradingForm(initial) {
    return {
        bands: initial,
        addBand() {
            this.bands.push({ grade: '', min_score: 0, max_score: 100, remark: '', gpa_point: 0 });
        },
    };
}
</script>
@endpush
@endsection
