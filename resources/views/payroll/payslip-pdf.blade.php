<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payslip — {{ $payslip->employee->full_name }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }
.page { padding: 28px 32px; max-width: 680px; margin: 0 auto; }

/* Header */
.header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 2px solid #7c3aed; padding-bottom: 14px; }
.header-left { display: table-cell; vertical-align: middle; width: 60%; }
.header-right { display: table-cell; vertical-align: middle; width: 40%; text-align: right; }
.school-name { font-size: 15px; font-weight: bold; color: #7c3aed; }
.school-sub { font-size: 9px; color: #555; margin-top: 2px; line-height: 1.5; }
.slip-label { font-size: 18px; font-weight: bold; letter-spacing: 2px; color: #7c3aed; }
.slip-period { font-size: 10px; color: #444; margin-top: 3px; }

/* Employee info */
.emp-row { display: table; width: 100%; margin-bottom: 16px; background: #f9f7ff; border: 1px solid #e9d5ff; border-radius: 4px; padding: 10px 14px; }
.emp-cell { display: table-cell; width: 33%; }
.emp-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #7c3aed; margin-bottom: 2px; }
.emp-value { font-size: 10px; font-weight: bold; color: #111; }

/* Tables */
table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
th { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: #7c3aed; padding: 5px 8px; text-align: left; }
th.r { text-align: right; }
td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
td.r { text-align: right; font-family: "Courier New", monospace; }

/* Summary box */
.summary { display: table; width: 100%; margin: 14px 0; }
.sum-cell { display: table-cell; width: 33%; text-align: center; padding: 12px 8px; }
.sum-cell.gross { background: #f9f7ff; border: 1px solid #e9d5ff; }
.sum-cell.ded   { background: #fff5f5; border: 1px solid #fecaca; }
.sum-cell.net   { background: #f0fdf4; border: 1px solid #a7f3d0; }
.sum-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #555; margin-bottom: 4px; }
.sum-amount { font-size: 16px; font-weight: bold; font-family: "Courier New", monospace; }
.sum-cell.gross .sum-amount { color: #7c3aed; }
.sum-cell.ded   .sum-amount { color: #dc2626; }
.sum-cell.net   .sum-amount { color: #065f46; }

/* Status */
.badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
.badge-paid     { background: #d1fae5; color: #065f46; }
.badge-pending  { background: #fef3c7; color: #92400e; }
.badge-approved { background: #dbeafe; color: #1e40af; }

/* Signature */
.sig-row { display: table; width: 100%; margin-top: 30px; }
.sig-cell { display: table-cell; width: 48%; text-align: center; }
.sig-line { border-top: 1px solid #333; width: 80%; margin: 0 auto 4px; }
.sig-label { font-size: 9px; color: #555; }

/* Footer */
.footer { text-align: center; margin-top: 16px; font-size: 8px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if ($school->logoUrl())
                <img src="{{ $school->logoUrl() }}" alt="Logo" style="height:44px; margin-bottom:5px; display:block;">
            @endif
            <div class="school-name">{{ $school->name }}</div>
            <div class="school-sub">
                {{ implode(' &bull; ', array_filter([$school->address, $school->city])) }}
                @if ($school->phone) &bull; {{ $school->phone }} @endif
            </div>
        </div>
        <div class="header-right">
            <div class="slip-label">PAY SLIP</div>
            <div class="slip-period">{{ $payslip->payrollRun->title }}</div>
            <div style="margin-top:6px;">
                <span class="badge badge-{{ $payslip->status }}">{{ ucfirst($payslip->status) }}</span>
            </div>
        </div>
    </div>

    {{-- Employee details --}}
    <div class="emp-row">
        <div class="emp-cell">
            <div class="emp-label">Employee Name</div>
            <div class="emp-value">{{ $payslip->employee->full_name }}</div>
        </div>
        <div class="emp-cell">
            <div class="emp-label">Employee No</div>
            <div class="emp-value">{{ $payslip->employee->employee_number }}</div>
        </div>
        <div class="emp-cell">
            <div class="emp-label">Position / Department</div>
            <div class="emp-value">{{ $payslip->employee->position?->title ?? '—' }}</div>
            <div style="font-size:9px; color:#555;">{{ $payslip->employee->department?->name ?? '' }}</div>
        </div>
    </div>

    {{-- Pay summary --}}
    <div class="summary">
        <div class="sum-cell gross">
            <div class="sum-label">Gross Pay</div>
            <div class="sum-amount">{{ $currency }}{{ number_format($payslip->gross_pay, 2) }}</div>
        </div>
        <div class="sum-cell ded">
            <div class="sum-label">Total Deductions</div>
            <div class="sum-amount">{{ $currency }}{{ number_format($payslip->total_deductions, 2) }}</div>
        </div>
        <div class="sum-cell net">
            <div class="sum-label">Net Pay</div>
            <div class="sum-amount">{{ $currency }}{{ number_format($payslip->net_pay, 2) }}</div>
        </div>
    </div>

    {{-- Earnings --}}
    <table>
        <thead>
            <tr>
                <th>Earnings</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="r">{{ $currency }}{{ number_format($payslip->basic_salary, 2) }}</td>
            </tr>
            @foreach (($payslip->allowances ?? []) as $name => $amount)
            @if ($amount > 0)
            <tr>
                <td>{{ ucwords(str_replace('_', ' ', $name)) }}</td>
                <td class="r">{{ $currency }}{{ number_format($amount, 2) }}</td>
            </tr>
            @endif
            @endforeach
            <tr style="font-weight:bold; border-top:2px solid #e5e7eb;">
                <td>Total Earnings</td>
                <td class="r">{{ $currency }}{{ number_format($payslip->gross_pay, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Deductions --}}
    @if (!empty($payslip->deductions))
    <table>
        <thead>
            <tr>
                <th>Deductions</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($payslip->deductions ?? []) as $name => $amount)
            @if ($amount > 0)
            <tr>
                <td>{{ ucwords(str_replace('_', ' ', $name)) }}</td>
                <td class="r" style="color:#dc2626;">{{ $currency }}{{ number_format($amount, 2) }}</td>
            </tr>
            @endif
            @endforeach
            <tr style="font-weight:bold; border-top:2px solid #e5e7eb;">
                <td>Total Deductions</td>
                <td class="r" style="color:#dc2626;">{{ $currency }}{{ number_format($payslip->total_deductions, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if ($payslip->notes)
    <div style="font-size:9px; color:#555; margin:8px 0; padding:8px; background:#f9f7ff; border-left:3px solid #7c3aed;">
        <strong>Notes:</strong> {{ $payslip->notes }}
    </div>
    @endif

    {{-- Signature --}}
    <div class="sig-row">
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-label">Employee Signature</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-label">Authorised By</div>
        </div>
    </div>

    <div class="footer">
        Confidential payslip for {{ $payslip->employee->full_name }} &bull; {{ $school->name }} &bull; Generated {{ now()->format('d M Y H:i') }}
    </div>

</div>
</body>
</html>
