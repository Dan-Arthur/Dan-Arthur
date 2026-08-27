<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view expenses'), 403);

        $schoolId = $this->schoolId();

        $from     = $request->get('from', now()->startOfMonth()->toDateString());
        $to       = $request->get('to',   now()->toDateString());
        $category = $request->get('category');
        $status   = $request->get('status');

        $query = Expense::where('school_id', $schoolId)
            ->whereBetween('date', [$from, $to])
            ->with(['recordedBy', 'approvedBy'])
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($status,   fn($q) => $q->where('status', $status))
            ->orderByDesc('date')
            ->orderByDesc('id');

        $expenses = $query->paginate(25)->withQueryString();

        // Summary totals for the filtered period
        $totals = Expense::where('school_id', $schoolId)
            ->whereBetween('date', [$from, $to])
            ->when($category, fn($q) => $q->where('category', $category))
            ->select('status', DB::raw('SUM(amount) as total', ), DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Category breakdown (approved only, for the period)
        $byCategory = Expense::where('school_id', $schoolId)
            ->whereBetween('date', [$from, $to])
            ->where('status', 'approved')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('expenses.index', compact(
            'expenses', 'totals', 'byCategory',
            'from', 'to', 'category', 'status', 'currency',
        ));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('create expenses'), 403);

        $categories     = Expense::CATEGORIES;
        $paymentMethods = Expense::PAYMENT_METHODS;
        $currency       = auth()->user()->school->currency_symbol ?? '₵';

        return view('expenses.create', compact('categories', 'paymentMethods', 'currency'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('create expenses'), 403);

        $validated = $request->validate([
            'category'         => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'date'             => 'required|date|before_or_equal:today',
            'paid_to'          => 'nullable|string|max:255',
            'payment_method'   => 'required|string|in:' . implode(',', array_keys(Expense::PAYMENT_METHODS)),
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:2000',
        ]);

        $expense = Expense::create(array_merge($validated, [
            'school_id'   => $this->schoolId(),
            'recorded_by' => auth()->id(),
            'status'      => 'pending',
        ]));

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense): View
    {
        abort_unless(auth()->user()->can('view expenses'), 403);
        abort_unless($expense->school_id == $this->schoolId(), 403);

        $expense->load(['recordedBy', 'approvedBy']);
        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('expenses.show', compact('expense', 'currency'));
    }

    public function edit(Expense $expense): View
    {
        abort_unless(auth()->user()->can('manage expenses'), 403);
        abort_unless($expense->school_id == $this->schoolId(), 403);
        abort_if($expense->status === 'approved', 403, 'Approved expenses cannot be edited.');

        $categories     = Expense::CATEGORIES;
        $paymentMethods = Expense::PAYMENT_METHODS;
        $currency       = auth()->user()->school->currency_symbol ?? '₵';

        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods', 'currency'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage expenses'), 403);
        abort_unless($expense->school_id == $this->schoolId(), 403);
        abort_if($expense->status === 'approved', 403, 'Approved expenses cannot be edited.');

        $validated = $request->validate([
            'category'         => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'date'             => 'required|date|before_or_equal:today',
            'paid_to'          => 'nullable|string|max:255',
            'payment_method'   => 'required|string|in:' . implode(',', array_keys(Expense::PAYMENT_METHODS)),
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:2000',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated.');
    }

    public function approve(Expense $expense): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve expenses'), 403);
        abort_unless($expense->school_id == $this->schoolId(), 403);
        abort_unless($expense->status === 'pending', 403);

        $expense->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense approved.');
    }

    public function reject(Expense $expense): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve expenses'), 403);
        abort_unless($expense->school_id == $this->schoolId(), 403);
        abort_unless($expense->status === 'pending', 403);

        $expense->update(['status' => 'rejected']);

        return back()->with('success', 'Expense rejected.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage expenses'), 403);
        abort_unless($expense->school_id == $this->schoolId(), 403);
        abort_if($expense->status === 'approved', 403, 'Approved expenses cannot be deleted.');

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted.');
    }
}
