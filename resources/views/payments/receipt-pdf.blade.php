<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt {{ $payment->receipt->receipt_number ?? $payment->payment_number }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }
.page { padding: 28px 32px; max-width: 600px; margin: 0 auto; }

/* Header */
.header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #065f46; padding-bottom: 14px; }
.school-name { font-size: 17px; font-weight: bold; color: #065f46; }
.school-sub { font-size: 9px; color: #555; margin-top: 3px; line-height: 1.6; }
.receipt-label { font-size: 18px; font-weight: bold; letter-spacing: 3px; color: #065f46; margin-top: 12px; }
.receipt-num { font-family: "Courier New", monospace; font-size: 12px; color: #333; margin-top: 3px; }

/* Info grid */
.info-row { display: table; width: 100%; margin-bottom: 5px; }
.info-label { display: table-cell; width: 40%; font-size: 10px; color: #555; }
.info-value { display: table-cell; width: 60%; font-size: 10px; font-weight: bold; color: #111; }

/* Divider */
.divider { border: none; border-top: 1px dashed #ccc; margin: 14px 0; }

/* Amount box */
.amount-box { background: #f0fdf4; border: 1px solid #6ee7b7; border-radius: 4px; padding: 14px; text-align: center; margin: 16px 0; }
.amount-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #065f46; margin-bottom: 4px; }
.amount-value { font-size: 26px; font-weight: bold; font-family: "Courier New", monospace; color: #065f46; }
.amount-words { font-size: 9px; color: #444; margin-top: 4px; font-style: italic; }

/* Invoice summary table */
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #555; background: #f8fafb; padding: 5px 8px; text-align: left; border-bottom: 1px solid #d1d5db; }
th.r { text-align: right; }
td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
td.r { text-align: right; font-family: "Courier New", monospace; }

/* Signatures */
.sig-row { display: table; width: 100%; margin-top: 28px; }
.sig-cell { display: table-cell; width: 48%; text-align: center; }
.sig-line { border-top: 1px solid #333; margin-bottom: 4px; width: 80%; margin-left: auto; margin-right: auto; }
.sig-label { font-size: 9px; color: #555; }

/* Footer */
.footer { text-align: center; margin-top: 20px; font-size: 8px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 8px; }

/* Stamp watermark for PAID */
.paid-stamp { position: relative; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        @if ($school->logoUrl())
            <img src="{{ $school->logoUrl() }}" alt="Logo" style="height:48px; display:block; margin:0 auto 6px;">
        @endif
        <div class="school-name">{{ $school->name }}</div>
        <div class="school-sub">
            {{ implode(' &bull; ', array_filter([$school->address, $school->city, $school->country])) }}<br>
            @if ($school->phone) Tel: {{ $school->phone }} @endif
            @if ($school->email) &nbsp;&bull;&nbsp; {{ $school->email }} @endif
        </div>
        <div class="receipt-label">OFFICIAL RECEIPT</div>
        <div class="receipt-num">{{ $payment->receipt->receipt_number ?? $payment->payment_number }}</div>
    </div>

    {{-- Payment details --}}
    <div class="info-row"><div class="info-label">Date Issued</div><div class="info-value">{{ $payment->payment_date->format('d M Y') }}</div></div>
    <div class="info-row"><div class="info-label">Received From</div><div class="info-value">{{ $payment->student->full_name }}</div></div>
    <div class="info-row"><div class="info-label">Admission No</div><div class="info-value">{{ $payment->student->admission_number }}</div></div>
    <div class="info-row"><div class="info-label">Invoice No</div><div class="info-value">{{ $payment->invoice->invoice_number }}</div></div>
    <div class="info-row"><div class="info-label">Payment Method</div><div class="info-value">{{ $payment->method_label }}</div></div>
    @if ($payment->reference_number)
    <div class="info-row"><div class="info-label">Reference</div><div class="info-value">{{ $payment->reference_number }}</div></div>
    @endif
    @if ($payment->bank_name)
    <div class="info-row"><div class="info-label">Bank</div><div class="info-value">{{ $payment->bank_name }}</div></div>
    @endif
    <div class="info-row"><div class="info-label">Academic Year</div><div class="info-value">{{ $payment->invoice->academicYear->name ?? '—' }}@if ($payment->invoice->term) &bull; {{ $payment->invoice->term->name }}@endif</div></div>

    {{-- Amount --}}
    <hr class="divider">
    <div class="amount-box">
        <div class="amount-label">Amount Received</div>
        <div class="amount-value">{{ $currency }}{{ number_format($payment->amount, 2) }}</div>
        <div class="amount-words">{{ $amountWords }}</div>
    </div>

    {{-- Invoice balance summary --}}
    <div style="font-size:9px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#555; margin-bottom:6px;">Invoice Summary</div>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="r">Total Invoice</th>
                <th class="r">Amount Paid</th>
                <th class="r">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->invoice->invoice_number }}</td>
                <td class="r">{{ $currency }}{{ number_format($payment->invoice->total_amount, 2) }}</td>
                <td class="r">{{ $currency }}{{ number_format($payment->invoice->amount_paid, 2) }}</td>
                <td class="r" style="{{ $payment->invoice->balance <= 0 ? 'color:#065f46;' : 'color:#991b1b;' }}">
                    {{ $currency }}{{ number_format($payment->invoice->balance, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    @if ($payment->notes)
    <div style="font-size:9px; color:#555; margin-top:8px; font-style:italic;">Notes: {{ $payment->notes }}</div>
    @endif

    {{-- Signature lines --}}
    <div class="sig-row">
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-label">Cashier / Received By</div>
            <div style="font-size:9px; color:#333; margin-top:2px;">{{ $payment->receivedBy?->name ?? '—' }}</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-label">Authorised Signatory</div>
        </div>
    </div>

    <div class="footer">
        This is an official receipt of {{ $school->name }}. Generated {{ now()->format('d M Y H:i') }}.
        Please retain for your records. E&amp;OE.
    </div>

</div>
</body>
</html>
