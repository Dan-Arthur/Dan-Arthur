@extends('layouts.app')

@section('title', 'Issue Book')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Issue Book</h1></div>
    <a href="{{ route('library.loans') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('library.issue.store') }}" class="max-w-lg card" x-data="issueForm()">
    @csrf

    <div class="space-y-4">
        <div class="form-group">
            <label class="form-label">Book <span class="required">*</span></label>
            <select name="book_id" class="form-select" required>
                <option value="">— Select Book —</option>
                @foreach ($books as $book)
                    <option value="{{ $book->id }}" {{ old('book_id', request('book_id')) == $book->id ? 'selected' : '' }}>
                        {{ $book->title }} — {{ $book->available_copies }} available
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Borrower Type <span class="required">*</span></label>
            <select name="member_type" class="form-select" x-model="memberType" required>
                <option value="student">Student</option>
                <option value="employee">Employee</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Borrower <span class="required">*</span></label>
            <input type="text" id="member-search" class="form-input"
                   placeholder="Type name…" autocomplete="off"
                   x-on:input.debounce.400ms="search($event.target.value)">
            <input type="hidden" name="member_id" x-model="memberId">
            <div x-show="suggestions.length" x-cloak class="relative">
                <ul class="absolute z-10 w-full bg-surface border border-border rounded-lg shadow-lg mt-1 py-1 max-h-48 overflow-y-auto">
                    <template x-for="s in suggestions" :key="s.id">
                        <li>
                            <button type="button" class="w-full text-left px-3 py-2 hover:bg-surface-hover text-sm"
                                    @click="select(s)">
                                <span x-text="s.full_name" class="font-medium"></span>
                                <span x-text="' (' + (s.admission_number || s.employee_number) + ')'" class="text-muted"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Loan Date <span class="required">*</span></label>
                <input type="date" name="loan_date" class="form-input" value="{{ old('loan_date', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Due Date <span class="required">*</span></label>
                <input type="date" name="due_date" class="form-input"
                       value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Issue Book</button>
            <a href="{{ route('library.loans') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
function issueForm() {
    return {
        memberType: 'student',
        memberId: '',
        suggestions: [],

        async search(q) {
            if (q.length < 2) { this.suggestions = []; return; }
            const url = this.memberType === 'student'
                ? `/students/search?q=${encodeURIComponent(q)}`
                : `/employees/search?q=${encodeURIComponent(q)}`;
            const resp = await fetch(url);
            this.suggestions = await resp.json();
        },

        select(s) {
            this.memberId = s.id;
            document.getElementById('member-search').value = s.full_name;
            this.suggestions = [];
        },
    };
}
</script>
@endpush
@endsection
