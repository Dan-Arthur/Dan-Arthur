@extends('layouts.app')

@section('title', 'New Fee Structure')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">New Fee Structure</h1>
        <p class="page-subtitle">Define fees for a class / term</p>
    </div>
    <a href="{{ route('fees.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('fees.store') }}" x-data="feeStructureForm()" class="max-w-3xl">
    @csrf

    <div class="card mb-6">
        <h2 class="card-title mb-4">Structure Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}"
                       placeholder="e.g. Term 1 Fees – JSS 1" required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Academic Year <span class="required">*</span></label>
                <select name="academic_year_id" class="form-select" required>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', $currentYear?->id) == $year->id ? 'selected' : '' }}>
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
                        <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
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
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                        <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Student Category</label>
                <input type="text" name="student_category" class="form-input"
                       value="{{ old('student_category') }}" placeholder="e.g. Boarder, Day">
            </div>

            <div class="form-group sm:col-span-2 flex items-center gap-2">
                <input type="checkbox" name="applies_to_all_classes" id="applies_all" value="1"
                       {{ old('applies_to_all_classes') ? 'checked' : '' }} class="form-checkbox">
                <label for="applies_all" class="form-label mb-0">Applies to all classes</label>
            </div>
        </div>
    </div>

    {{-- Fee Items --}}
    <div class="card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="card-title">Fee Items</h2>
            @if ($categories->isNotEmpty())
            <button type="button" @click="addFromTemplate()" class="btn btn-ghost btn-sm">
                + Quick Add All Categories
            </button>
            @endif
        </div>

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
                                       class="form-input" placeholder="Fee description" required>
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
                                       class="form-input text-right" placeholder="0.00" min="0" step="0.01" required>
                            </td>
                            <td class="py-2 text-center">
                                <input type="checkbox" :name="`items[${index}][is_mandatory]`" x-model="item.is_mandatory"
                                       value="1" class="form-checkbox">
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
        @error('items')<p class="form-error mt-2">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Create Fee Structure</button>
        <a href="{{ route('fees.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
function feeStructureForm() {
    return {
        items: [{ name: '', fee_category_id: '', amount: '', is_mandatory: true }],

        addItem() {
            this.items.push({ name: '', fee_category_id: '', amount: '', is_mandatory: true });
        },

        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },

        addFromTemplate() {
            @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])).forEach(cat => {
                if (!this.items.some(i => i.fee_category_id == cat.id)) {
                    this.items.push({ name: cat.name, fee_category_id: cat.id, amount: '', is_mandatory: true });
                }
            });
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
