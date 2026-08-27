<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\SmsAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    // ---------------------------------------------------------------
    // Index — list payroll runs
    // ---------------------------------------------------------------

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view payroll'), 403);

        $runs = PayrollRun::where('school_id', $this->schoolId())
            ->with('runner')
            ->withCount('payslips')
            ->orderByDesc('year')->orderByDesc('month')
            ->paginate(20);

        return view('payroll.index', compact('runs'));
    }

    // ---------------------------------------------------------------
    // Create / Store — start a new payroll run
    // ---------------------------------------------------------------

    public function create(): View
    {
        abort_unless(auth()->user()->can('manage payroll'), 403);

        $months = PayrollRun::MONTHS;
        $years  = range(date('Y'), date('Y') - 5);

        return view('payroll.create', compact('months', 'years'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage payroll'), 403);

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'notes' => 'nullable|string|max:1000',
        ]);

        $schoolId = $this->schoolId();

        // Prevent duplicate runs for same month/year
        $exists = PayrollRun::where('school_id', $schoolId)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'A payroll run for ' . PayrollRun::MONTHS[$validated['month']] . ' ' . $validated['year'] . ' already exists.');
        }

        $run = DB::transaction(function () use ($validated, $schoolId) {
            $run = PayrollRun::create([
                'school_id' => $schoolId,
                'run_by'    => auth()->id(),
                'title'     => PayrollRun::MONTHS[$validated['month']] . ' ' . $validated['year'] . ' Payroll',
                'month'     => $validated['month'],
                'year'      => $validated['year'],
                'status'    => 'draft',
                'notes'     => $validated['notes'] ?? null,
            ]);

            // Generate a payslip for every active employee
            $employees = Employee::where('school_id', $schoolId)
                ->where('status', 'active')
                ->get();

            foreach ($employees as $emp) {
                $basic = $emp->basic_salary ?? 0;
                // Default deductions: SSNIT 5.5%
                $ssnit = round($basic * 0.055, 2);
                $deductions = $ssnit > 0 ? ['SSNIT (5.5%)' => $ssnit] : [];
                $gross = $basic;
                $net   = max(0, $gross - $ssnit);

                Payslip::create([
                    'school_id'       => $schoolId,
                    'payroll_run_id'  => $run->id,
                    'employee_id'     => $emp->id,
                    'basic_salary'    => $basic,
                    'allowances'      => [],
                    'deductions'      => $deductions,
                    'gross_pay'       => $gross,
                    'total_deductions'=> $ssnit,
                    'net_pay'         => $net,
                    'status'          => 'draft',
                ]);
            }

            $run->recalculate();

            return $run;
        });

        return redirect()->route('payroll.show', $run)
            ->with('success', 'Payroll run created with ' . $run->payslips()->count() . ' payslips.');
    }

    // ---------------------------------------------------------------
    // Show — payroll run detail
    // ---------------------------------------------------------------

    public function show(PayrollRun $payrollRun): View
    {
        abort_unless(auth()->user()->can('view payroll'), 403);
        abort_unless($payrollRun->school_id == $this->schoolId(), 403);

        $payrollRun->load('runner');

        $payslips = $payrollRun->payslips()
            ->with(['employee.position', 'employee.department'])
            ->orderBy('employee_id')
            ->get();

        // Group by staff type
        $byType = $payslips->groupBy(fn($p) => $p->employee->position?->type ?? 'other');

        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('payroll.show', compact('payrollRun', 'payslips', 'byType', 'currency'));
    }

    // ---------------------------------------------------------------
    // Approve & Mark Paid
    // ---------------------------------------------------------------

    public function approve(PayrollRun $payrollRun): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage payroll'), 403);
        abort_unless($payrollRun->school_id == $this->schoolId(), 403);

        if ($payrollRun->status !== 'draft') {
            return back()->with('error', 'Only draft payroll runs can be approved.');
        }

        $payrollRun->update(['status' => 'approved', 'approved_at' => now()]);

        return back()->with('success', 'Payroll approved.');
    }

    public function markPaid(PayrollRun $payrollRun): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage payroll'), 403);
        abort_unless($payrollRun->school_id == $this->schoolId(), 403);

        if ($payrollRun->status !== 'approved') {
            return back()->with('error', 'Only approved payroll runs can be marked as paid.');
        }

        $payrollRun->update(['status' => 'paid', 'paid_at' => now()]);
        $payrollRun->payslips()->update(['status' => 'paid']);

        // Send SMS alert to every employee with a phone number
        $this->sendPayrollSms($payrollRun);

        return back()->with('success', 'Payroll marked as paid. Payslip SMS alerts sent to staff.');
    }

    // ---------------------------------------------------------------
    // SMS notification on payroll payment
    // ---------------------------------------------------------------

    private function sendPayrollSms(PayrollRun $run): void
    {
        $schoolId = $run->school_id;
        $school   = auth()->user()->school;
        $currency = $school->currency_symbol ?? '₵';

        // Build per-employee messages and collect phones
        $payslips = $run->payslips()->with('employee')->get();
        $phones   = [];

        foreach ($payslips as $slip) {
            $phone = $slip->employee->phone ?? null;
            if ($phone && trim($phone) !== '') {
                $phones[] = trim($phone);
            }
        }

        $phones = array_values(array_unique(array_filter($phones)));

        if (empty($phones)) {
            return;
        }

        $body = "Dear Staff, your {$run->period_label} salary has been processed. "
              . "Please check your payslip for details. — Johan International School";

        SmsAlert::create([
            'school_id'        => $schoolId,
            'sender_id'        => auth()->id(),
            'body'             => $body,
            'recipient_group'  => 'all_staff',
            'class_id'         => null,
            'recipients_count' => count($phones),
            'phone_numbers'    => $phones,
            'status'           => 'sent',
            'sent_at'          => now(),
        ]);
    }

    // ---------------------------------------------------------------
    // Individual Payslip — view & edit
    // ---------------------------------------------------------------

    public function payslip(Payslip $payslip): View
    {
        abort_unless(auth()->user()->can('view payroll'), 403);
        abort_unless($payslip->school_id == $this->schoolId(), 403);

        $payslip->load(['employee.position', 'employee.department', 'payrollRun']);
        $currency = auth()->user()->school->currency_symbol ?? '₵';

        return view('payroll.payslip', compact('payslip', 'currency'));
    }

    public function updatePayslip(Request $request, Payslip $payslip): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage payroll'), 403);
        abort_unless($payslip->school_id == $this->schoolId(), 403);

        if ($payslip->payrollRun->status === 'paid') {
            return back()->with('error', 'Cannot edit a payslip from a paid payroll run.');
        }

        $validated = $request->validate([
            'basic_salary'               => 'required|numeric|min:0',
            'allowance_keys'             => 'nullable|array',
            'allowance_keys.*'           => 'nullable|string|max:100',
            'allowance_values'           => 'nullable|array',
            'allowance_values.*'         => 'nullable|numeric|min:0',
            'deduction_keys'             => 'nullable|array',
            'deduction_keys.*'           => 'nullable|string|max:100',
            'deduction_values'           => 'nullable|array',
            'deduction_values.*'         => 'nullable|numeric|min:0',
            'notes'                      => 'nullable|string|max:500',
        ]);

        // Rebuild allowances/deductions from parallel key/value arrays
        $allowances = [];
        foreach (($validated['allowance_keys'] ?? []) as $i => $key) {
            $key = trim($key);
            $val = (float) ($validated['allowance_values'][$i] ?? 0);
            if ($key !== '' && $val > 0) {
                $allowances[$key] = $val;
            }
        }

        $deductions = [];
        foreach (($validated['deduction_keys'] ?? []) as $i => $key) {
            $key = trim($key);
            $val = (float) ($validated['deduction_values'][$i] ?? 0);
            if ($key !== '' && $val > 0) {
                $deductions[$key] = $val;
            }
        }

        $payslip->update([
            'basic_salary' => $validated['basic_salary'],
            'allowances'   => $allowances,
            'deductions'   => $deductions,
            'notes'        => $validated['notes'] ?? null,
        ]);

        $payslip->recalculate();
        $payslip->payrollRun->recalculate();

        return redirect()->route('payroll.show', $payslip->payroll_run_id)
            ->with('success', 'Payslip updated.');
    }
}
