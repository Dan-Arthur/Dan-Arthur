<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\StudentScholarship;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    private function gate(string $permission = 'create invoices'): void
    {
        abort_unless(auth()->user()->can($permission), 403);
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->canany(['create invoices', 'edit invoices', 'record payments']), 403);

        $schoolId = auth()->user()->school_id;

        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();

        $query = Invoice::where('invoices.school_id', $schoolId)
            ->with(['student', 'academicYear', 'term'])
            ->select('invoices.*');

        if ($request->filled('year_id')) {
            $query->where('academic_year_id', $request->integer('year_id'));
        } elseif ($currentYear) {
            $query->where('academic_year_id', $currentYear->id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->integer('term_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('student', fn($q) => $q->where('first_name', 'like', $search)
                ->orWhere('last_name', 'like', $search)
                ->orWhere('admission_number', 'like', $search));
        }

        $invoices = $query->orderByDesc('issue_date')->paginate(30)->withQueryString();

        $terms = $currentYear ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get() : collect();

        return view('invoices.index', compact('invoices', 'years', 'terms', 'currentYear'));
    }

    public function create(Request $request): View
    {
        $this->gate('create invoices');

        $schoolId = auth()->user()->school_id;

        $years       = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();
        $terms       = $currentYear ? Term::where('academic_year_id', $currentYear->id)->orderBy('sequence')->get() : collect();
        $scholarships = Scholarship::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $structures  = FeeStructure::where('school_id', $schoolId)->where('is_active', true)
            ->with('items.feeCategory')
            ->orderBy('name')->get();

        $student = null;
        $preselectedScholarshipId = null;
        if ($request->filled('student_id')) {
            $student = Student::where('school_id', $schoolId)->find($request->integer('student_id'));
            if ($student && $currentYear) {
                $preselectedScholarshipId = StudentScholarship::where('student_id', $student->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->where('school_id', $schoolId)
                    ->value('scholarship_id');
            }
        }

        return view('invoices.create', compact(
            'years', 'terms', 'scholarships', 'structures',
            'student', 'currentYear', 'preselectedScholarshipId',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->gate('create invoices');

        $validated = $request->validate([
            'student_id'         => 'required|exists:students,id',
            'academic_year_id'   => 'required|exists:academic_years,id',
            'term_id'            => 'nullable|exists:terms,id',
            'fee_structure_id'   => 'nullable|exists:fee_structures,id',
            'issue_date'         => 'required|date',
            'due_date'           => 'nullable|date|after_or_equal:issue_date',
            'discount_amount'    => 'nullable|numeric|min:0',
            'scholarship_id'     => 'nullable|exists:scholarships,id',
            'notes'              => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:300',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.discount'         => 'nullable|numeric|min:0',
            'items.*.fee_category_id'  => 'nullable|exists:fee_categories,id',
        ]);

        $schoolId = auth()->user()->school_id;

        abort_unless(
            Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->exists(),
            403
        );

        $invoice = DB::transaction(function () use ($validated, $request, $schoolId) {
            $subtotal = 0;
            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += max(0, $lineTotal);
                $itemsData[] = [
                    'fee_category_id' => $item['fee_category_id'] ?? null,
                    'description'     => $item['description'],
                    'unit_price'      => $item['unit_price'],
                    'quantity'        => $item['quantity'],
                    'discount'        => $item['discount'] ?? 0,
                    'total'           => max(0, $lineTotal),
                ];
            }

            $discountAmount = $validated['discount_amount'] ?? 0;

            $scholarshipAmount = 0;
            if (!empty($validated['scholarship_id'])) {
                $scholarship = Scholarship::find($validated['scholarship_id']);
                if ($scholarship) {
                    $scholarshipAmount = $scholarship->type === 'percentage'
                        ? round($subtotal * $scholarship->value / 100, 2)
                        : $scholarship->value;
                }
            }

            $total = max(0, $subtotal - $discountAmount - $scholarshipAmount);

            $invoice = Invoice::create([
                'school_id'          => $schoolId,
                'student_id'         => $validated['student_id'],
                'academic_year_id'   => $validated['academic_year_id'],
                'term_id'            => $validated['term_id'] ?? null,
                'fee_structure_id'   => $validated['fee_structure_id'] ?? null,
                'invoice_number'     => Invoice::nextNumber($schoolId),
                'issue_date'         => $validated['issue_date'],
                'due_date'           => $validated['due_date'] ?? null,
                'subtotal'           => $subtotal,
                'discount_amount'    => $discountAmount,
                'scholarship_amount' => $scholarshipAmount,
                'total_amount'       => $total,
                'amount_paid'        => 0,
                'balance'            => $total,
                'status'             => 'unpaid',
                'notes'              => $validated['notes'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): View
    {
        abort_unless(auth()->user()->canany(['create invoices', 'edit invoices', 'record payments']), 403);
        abort_unless($invoice->school_id == auth()->user()->school_id, 403);

        $invoice->load([
            'student',
            'academicYear',
            'term',
            'feeStructure',
            'items.feeCategory',
            'payments.receivedBy',
            'payments.receipt',
            'createdBy',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        $this->gate('edit invoices');
        abort_unless($invoice->school_id == auth()->user()->school_id, 403);

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel a paid or already cancelled invoice.');
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice cancelled.');
    }

    // --------------------------------------------------------
    // AJAX endpoint: load fee structure items for auto-populate
    // --------------------------------------------------------

    public function structureItems(FeeStructure $fee): \Illuminate\Http\JsonResponse
    {
        abort_unless($fee->school_id == auth()->user()->school_id, 403);

        $items = $fee->items()->with('feeCategory')->orderBy('sort_order')->get()
            ->map(fn($i) => [
                'id'              => $i->id,
                'fee_category_id' => $i->fee_category_id,
                'category_name'   => $i->feeCategory?->name ?? '',
                'description'     => $i->name,
                'unit_price'      => $i->amount,
                'quantity'        => 1,
                'discount'        => 0,
                'total'           => $i->amount,
            ]);

        return response()->json($items);
    }
}
