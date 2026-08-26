<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view payments'), 403);

        $schoolId = auth()->user()->school_id;

        $query = Payment::where('school_id', $schoolId)
            ->with(['student', 'invoice', 'receivedBy', 'receipt'])
            ->latest('payment_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', $search)
                  ->orWhere('reference_number', 'like', $search)
                  ->orWhereHas('student', fn($sq) => $sq->where('first_name', 'like', $search)
                      ->orWhere('last_name', 'like', $search));
            });
        }

        $payments = $query->paginate(30)->withQueryString();

        $methods  = Invoice::METHODS;
        $statuses = Payment::STATUSES;

        return view('payments.index', compact('payments', 'methods', 'statuses'));
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->can('record payments'), 403);

        $schoolId = auth()->user()->school_id;

        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::where('school_id', $schoolId)
                ->with(['student', 'items'])
                ->find($request->integer('invoice_id'));
        }

        $methods = Invoice::METHODS;

        return view('payments.create', compact('invoice', 'methods'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('record payments'), 403);

        $validated = $request->validate([
            'invoice_id'       => 'required|exists:invoices,id',
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_method'   => 'required|in:' . implode(',', array_keys(Invoice::METHODS)),
            'reference_number' => 'nullable|string|max:100',
            'bank_name'        => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        $schoolId = auth()->user()->school_id;

        $invoice = Invoice::where('school_id', $schoolId)->findOrFail($validated['invoice_id']);

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot record payment on a cancelled invoice.');
        }

        $payment = null;

        DB::transaction(function () use ($validated, $invoice, $schoolId, &$payment) {
            $payment = Payment::create([
                'school_id'        => $schoolId,
                'invoice_id'       => $invoice->id,
                'student_id'       => $invoice->student_id,
                'payment_number'   => Payment::nextNumber($schoolId),
                'amount'           => $validated['amount'],
                'payment_date'     => $validated['payment_date'],
                'payment_method'   => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'bank_name'        => $validated['bank_name'] ?? null,
                'status'           => 'confirmed',
                'notes'            => $validated['notes'] ?? null,
                'received_by'      => auth()->id(),
            ]);

            if (auth()->user()->can('generate receipts')) {
                Receipt::create([
                    'payment_id'     => $payment->id,
                    'receipt_number' => Receipt::nextNumber($schoolId),
                    'issued_at'      => now(),
                    'issued_by'      => auth()->id(),
                ]);
            }

            $invoice->recalculate();
        });

        return redirect()->route('payments.show', $payment)->with('success', 'Payment recorded and receipt issued.');
    }

    public function show(Payment $payment): View
    {
        abort_unless(auth()->user()->can('view payments'), 403);
        abort_unless($payment->school_id == auth()->user()->school_id, 403);

        $payment->load(['student', 'invoice.items', 'receivedBy', 'receipt.issuedBy']);

        $methods = Invoice::METHODS;

        return view('payments.show', compact('payment', 'methods'));
    }

    public function reverse(Payment $payment): RedirectResponse
    {
        abort_unless(auth()->user()->can('record payments'), 403);
        abort_unless($payment->school_id == auth()->user()->school_id, 403);

        if ($payment->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed payments can be reversed.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'reversed']);
            $payment->invoice->recalculate();
        });

        return back()->with('success', 'Payment reversed. Invoice balance updated.');
    }
}
