@extends('layouts.app')

@section('title', 'New Invoice')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">New Invoice</h1>
        <p class="page-subtitle">Create a student fee invoice</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('invoices.store') }}" x-data="invoiceForm()" class="max-w-3xl">
    @csrf

    <div class="card mb-6">
        <h2 class="card-title mb-4">Invoice Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Student <span class="required">*</span></label>
                <input type="text" id="student-search" class="form-input" placeholder="Type student name or admission no…"
                       value="{{ $student ? $student->full_name . ' (' . $student->admission_number . ')' : '' }}"
                       autocomplete="off"
                       x-on:input.debounce.400ms="searchStudents($event.target.value)">
                <input type="hidden" name="student_id" x-model="studentId" value="{{ $student?->id }}">
                <div x-show="suggestions.length" x-cloak class="relative">
                    <ul class="absolute z-10 w-full bg-surface border border-border rounded-lg shadow-lg mt-1 py-1 max-h-48 overflow-y-auto">
                        <template x-for="s in suggestions" :key="s.id">
                            <li>
                                <button type="button" class="w-full text-left px-3 py-2 hover:bg-surface-hover text-sm"
                                        @click="selectStudent(s)">
                                    <span x-text="s.full_name" class="font-medium"></span>
                                    <span x-text="' (' + s.admission_number + ')'" class="text-muted"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
                @error('student_id')<p class="form-error">{{ $message }}</p>@enderror
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
                    <option value="">Select Term</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Issue Date <span class="required">*</span></label>
                <input type="date" name="issue_date" class="form-input" value="{{ old('issue_date', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-input" value="{{ old('due_date') }}">
            </div>

            <div class="sm:col-span-2 form-group">
                <label class="form-label">Load from Fee Structure</label>
                <div class="flex gap-2">
                    <select id="fee-structure-picker" class="form-select flex-1" @change="loadStructure($event.target.value)">
                        <option value="">— Select to auto-populate items —</option>
                        @foreach ($structures as $structure)
                            <option value="{{ $structure->id }}">{{ $structure->name }} ({{ number_format($structure->total, 2) }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="fee_structure_id" x-model="feeStructureId">
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card mb-6">
        <h2 class="card-title mb-4">Line Items</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-muted border-b border-border">
                        <th class="pb-2 font-medium w-2/5">Description</th>
                        <th class="pb-2 font-medium text-right w-28">Unit Price</th>
                        <th class="pb-2 font-medium text-right w-20">Qty</th>
                        <th class="pb-2 font-medium text-right w-24">Discount</th>
                        <th class="pb-2 font-medium text-right w-28">Total</th>
                        <th class="pb-2 w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="index">
                        <tr class="border-b border-border/50">
                            <td class="py-2 pr-2">
                                <input type="text" :name="`items[${index}][description]`" x-model="item.description"
                                       class="form-input" required>
                                <input type="hidden" :name="`items[${index}][fee_category_id]`" x-model="item.fee_category_id">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="number" :name="`items[${index}][unit_price]`" x-model="item.unit_price"
                                       class="form-input text-right" min="0" step="0.01" required @input="recalc()">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity"
                                       class="form-input text-right" min="1" required @input="recalc()">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="number" :name="`items[${index}][discount]`" x-model="item.discount"
                                       class="form-input text-right" min="0" step="0.01" @input="recalc()">
                            </td>
                            <td class="py-2 pr-2 text-right font-mono"
                                x-text="lineTotal(item).toLocaleString('en', {minimumFractionDigits:2})"></td>
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
                        <td colspan="6" class="pt-2">
                            <button type="button" @click="addItem()" class="btn btn-ghost btn-sm">+ Add Row</button>
                        </td>
                    </tr>
                    <tr class="border-t border-border">
                        <td colspan="4" class="pt-2 text-right text-muted text-sm">Subtotal</td>
                        <td class="pt-2 text-right font-mono" x-text="subtotal().toLocaleString('en',{minimumFractionDigits:2})"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Discounts --}}
    <div class="card mb-6">
        <h2 class="card-title mb-4">Adjustments</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Manual Discount</label>
                <input type="number" name="discount_amount" class="form-input" value="{{ old('discount_amount', 0) }}"
                       min="0" step="0.01" x-model="manualDiscount" @input="recalc()">
            </div>
            <div class="form-group">
                <label class="form-label">Scholarship</label>
                <select name="scholarship_id" class="form-select" @change="recalc()">
                    <option value="">No Scholarship</option>
                    @foreach ($scholarships as $scholarship)
                        <option value="{{ $scholarship->id }}"
                                data-type="{{ $scholarship->type }}" data-value="{{ $scholarship->value }}"
                                {{ old('scholarship_id') == $scholarship->id ? 'selected' : '' }}>
                            {{ $scholarship->name }}
                            ({{ $scholarship->type === 'percentage' ? $scholarship->value . '%' : 'Fixed ' . number_format($scholarship->value, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 bg-surface-hover rounded-lg p-4 text-sm space-y-1">
                <div class="flex justify-between">
                    <span class="text-muted">Subtotal</span>
                    <span class="font-mono" x-text="subtotal().toLocaleString('en',{minimumFractionDigits:2})"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Discount</span>
                    <span class="font-mono text-danger" x-text="'- ' + parseFloat(manualDiscount||0).toLocaleString('en',{minimumFractionDigits:2})"></span>
                </div>
                <div class="flex justify-between font-semibold text-base border-t border-border pt-2 mt-2">
                    <span>Total Due</span>
                    <span class="font-mono" x-text="grandTotal().toLocaleString('en',{minimumFractionDigits:2})"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mb-6">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-textarea" rows="3">{{ old('notes') }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Create Invoice</button>
        <a href="{{ route('invoices.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
function invoiceForm() {
    return {
        studentId: '{{ $student?->id ?? '' }}',
        suggestions: [],
        feeStructureId: '',
        items: [{ description: '', fee_category_id: '', unit_price: '', quantity: 1, discount: 0 }],
        manualDiscount: 0,

        async searchStudents(q) {
            if (q.length < 2) { this.suggestions = []; return; }
            const resp = await fetch(`/students/search?q=${encodeURIComponent(q)}`);
            this.suggestions = await resp.json();
        },

        selectStudent(s) {
            this.studentId = s.id;
            document.getElementById('student-search').value = s.full_name + ' (' + s.admission_number + ')';
            this.suggestions = [];
        },

        async loadStructure(id) {
            this.feeStructureId = id;
            if (!id) return;
            const resp = await fetch(`/fees/${id}/items`);
            const data = await resp.json();
            if (data.length) {
                this.items = data.map(i => ({
                    description: i.description,
                    fee_category_id: i.fee_category_id || '',
                    unit_price: i.unit_price,
                    quantity: 1,
                    discount: 0,
                }));
            }
        },

        addItem() {
            this.items.push({ description: '', fee_category_id: '', unit_price: '', quantity: 1, discount: 0 });
        },

        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },

        lineTotal(item) {
            return Math.max(0, (parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 1) - (parseFloat(item.discount) || 0));
        },

        subtotal() {
            return this.items.reduce((acc, i) => acc + this.lineTotal(i), 0);
        },

        grandTotal() {
            return Math.max(0, this.subtotal() - (parseFloat(this.manualDiscount) || 0));
        },

        recalc() { /* triggers reactivity */ },
    };
}
</script>
@endpush
@endsection
