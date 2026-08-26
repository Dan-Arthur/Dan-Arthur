@extends('layouts.app')

@section('title', 'Edit Fee Structure')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Fee Structure</h1>
        <p class="page-subtitle">{{ $fee->name }}</p>
    </div>
    <a href="{{ route('fees.show', $fee) }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('fees.update', $fee) }}" x-data="feeStructureEdit()" class="max-w-3xl">
    @csrf @method('PUT')

    <div class="card mb-6">
        <h2 class="card-title mb-4">Structure Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $fee->name) }}" required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Academic Year <span class="required">*</span></label>
                <select name="academic_year_id" class="form-select" required>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', $fee->academic_year_id) == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select">
                    <option value="">All Terms</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" {{ old('term_id', $fee->term_id) == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $fee->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">All Campuses</option>
                    @foreach ($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ old('campus_id', $fee->campus_id) == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Student Category</label>
                <input type="text" name="student_category" class="form-input"
                       value="{{ old('student_category', $fee->student_category) }}">
            </div>

            <div class="form-group sm:col-span-2 flex items-center gap-2">
                <input type="checkbox" name="applies_to_all_classes" id="applies_all" value="1"
                       {{ old('applies_to_all_classes', $fee->applies_to_all_classes) ? 'checked' : '' }} class="form-checkbox">
                <label for="applies_all" class="form-label mb-0">Applies to all classes</label>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <h2 class="card-title mb-4">Fee Items</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-muted border-b border-border">
                        <th class="pb-2 font-medium w-1/3">Description</th>
                        <th class="pb-2 font-medium">Category</th>
                        <th class="pb-2 font-medium text-right">Amount</th>
                        <th class="pb-2 font-medium text-center">Mandatory</th>
                        <th class="pb-2 w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="index">
                        <tr class="border-b border-border/50">
                            <td class="py-2 pr-2">
                                <input type="text" :name="`items[${index}][name]`" x-model="item.name"
                                       class="form-input" required>
                            </td>
                            <td class="py-2 pr-2">
                                <select :name="`items[${index}][fee_category_id]`" x-model="item.fee_category_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 pr-2 w-36">
                                <input type="number" :name="`items[${index}][amount]`" x-model="item.amount"
                                       class="form-input text-right" min="0" step="0.01" required>
                            </td>
                            <td class="py-2 text-center">
                                <input type="checkbox" :name="`items[${index}][is_mandatory]`"
                                       x-model="item.is_mandatory" value="1" class="form-checkbox">
                            </td>
                            <td class="py-2 text-center">
                                <button type="button" @click="removeItem(index)" class="text-danger hover:opacity-75"
                                        x-show="items.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="pt-3">
                            <button type="button" @click="addItem()" class="btn btn-ghost btn-sm">+ Add Row</button>
                        </td>
                    </tr>
                    <tr class="border-t-2 border-border">
                        <td colspan="2" class="pt-3 font-semibold text-right pr-2">Total</td>
                        <td class="pt-3 font-semibold text-right font-mono" x-text="totalAmount()"></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Update Fee Structure</button>
        <a href="{{ route('fees.show', $fee) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>

@php
$feeItemsJson = $fee->items->map(fn($i) => [
    'name' => $i->name,
    'fee_category_id' => $i->fee_category_id ?? '',
    'amount' => $i->amount,
    'is_mandatory' => (bool)$i->is_mandatory,
])->values()->toJson();
@endphp
@push('scripts')
<script>
function feeStructureEdit() {
    return {
        items: {!! $feeItemsJson !!},

        addItem() {
            this.items.push({ name: '', fee_category_id: '', amount: '', is_mandatory: true });
        },

        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },

        totalAmount() {
            const sum = this.items.reduce((acc, i) => acc + (parseFloat(i.amount) || 0), 0);
            return sum.toLocaleString('en', { minimumFractionDigits: 2 });
        },
    };
}
</script>
@endpush
@endsection
