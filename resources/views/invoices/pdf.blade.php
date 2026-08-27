<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

.page { padding: 28px 32px; }

/* Header */
.header { display: table; width: 100%; margin-bottom: 24px; border-bottom: 2px solid #1e40af; padding-bottom: 16px; }
.header-left { display: table-cell; vertical-align: middle; width: 60%; }
.header-right { display: table-cell; vertical-align: middle; width: 40%; text-align: right; }
.school-name { font-size: 16px; font-weight: bold; color: #1e40af; }
.school-sub { font-size: 9px; color: #555; margin-top: 2px; line-height: 1.5; }
.invoice-label { font-size: 22px; font-weight: bold; color: #1e40af; letter-spacing: 2px; }
.invoice-num { font-family: "Courier New", monospace; font-size: 12px; color: #444; margin-top: 3px; }

/* Meta grid */
.meta { display: table; width: 100%; margin-bottom: 20px; }
.meta-cell { display: table-cell; width: 50%; vertical-align: top; }
.meta-cell.right { text-align: right; }
.label { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
.value { font-size: 11px; font-weight: bold; color: #111; }
.value-sub { font-size: 9px; color: #555; }

/* Status badge */
.badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
.badge-unpaid   { background: #fef3c7; color: #92400e; }
.badge-partial  { background: #dbeafe; color: #1e40af; }
.badge-paid     { background: #d1fae5; color: #065f46; }
.badge-overdue  { background: #fee2e2; color: #991b1b; }
.badge-cancelled{ background: #f3f4f6; color: #374151; }

/* Section heading */
.section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1e40af; margin-bottom: 6px; border-bottom: 1px solid #dbeafe; padding-bottom: 3px; }

/* Table */
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
th { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #555; background: #f8faff; padding: 6px 8px; text-align: left; border-bottom: 1px solid #d1d5db; }
th.r { text-align: right; }
td { padding: 6px 8px; border-bottom: 1px solid #f0f0f0; font-size: 10px; vertical-align: top; }
td.r { text-align: right; font-family: "Courier New", monospace; }
td.muted { color: #666; font-size: 9px; }

/* Totals */
.totals { width: 40%; margin-left: 60%; }
.totals table { margin-bottom: 0; }
.totals td { padding: 3px 8px; border: none; }
.totals td.r { font-family: "Courier New", monospace; }
.totals .grand { font-weight: bold; font-size: 12px; border-top: 2px solid #1e40af; }
.totals .balance { font-weight: bold; font-size: 13px; color: #991b1b; }
.totals .balance.paid { color: #065f46; }

/* Notes */
.notes { background: #f8faff; border-left: 3px solid #1e40af; padding: 8px 12px; margin-bottom: 16px; font-size: 10px; color: #444; }

/* Footer */
.footer { border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px; font-size: 9px; color: #888; }
.footer-row { display: table; width: 100%; }
.footer-left { display: table-cell; }
.footer-right { display: table-cell; text-align: right; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if ($school->logoUrl())
                <img src="{{ $school->logoUrl() }}" alt="Logo" style="height:50px; margin-bottom:6px; display:block;">
            @endif
            <div class="school-name">{{ $school->name }}</div>
            <div class="school-sub">
                {{ implode(' &bull; ', array_filter([$school->address, $school->city, $school->country])) }}<br>
                @if ($school->phone) Tel: {{ $school->phone }} @endif
                @if ($school->email) &nbsp;&bull;&nbsp; {{ $school->email }} @endif
                @if ($school->motto) <br><em>{{ $school->motto }}</em> @endif
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-label">INVOICE</div>
            <div class="invoice-num">{{ $invoice->invoice_number }}</div>
            <div style="margin-top:8px;">
                <span class="badge badge-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
            </div>
        </div>
    </div>

    {{-- Bill to / dates --}}
    <div class="meta" style="margin-bottom:18px;">
        <div class="meta-cell">
            <div class="label">Bill To</div>
            <div class="value">{{ $invoice->student->full_name }}</div>
            <div class="value-sub">Admission No: {{ $invoice->student->admission_number }}</div>
            @if ($invoice->student->classroom)
                <div class="value-sub">Class: {{ $invoice->student->classroom->name }}</div>
            @endif
        </div>
        <div class="meta-cell right">
            <div class="label">Issue Date</div>
            <div class="value">{{ $invoice->issue_date->format('d M Y') }}</div>
            @if ($invoice->due_date)
                <div class="label" style="margin-top:8px;">Due Date</div>
                <div class="value">{{ $invoice->due_date->format('d M Y') }}</div>
            @endif
            <div class="label" style="margin-top:8px;">Academic Year</div>
            <div class="value-sub">
                {{ $invoice->academicYear->name }}
                @if ($invoice->term) &bull; {{ $invoice->term->name }} @endif
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="section-title">Line Items</div>
    <table>
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th class="r" style="width:18%">Unit Price</th>
                <th class="r" style="width:8%">Qty</th>
                <th class="r" style="width:16%">Discount</th>
                <th class="r" style="width:18%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
            <tr>
                <td>
                    {{ $item->description }}
                    @if ($item->feeCategory)
                        <br><span class="muted">{{ $item->feeCategory->name }}</span>
                    @endif
                </td>
                <td class="r">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                <td class="r">{{ $item->quantity }}</td>
                <td class="r">{{ $item->discount > 0 ? $currency . number_format($item->discount, 2) : '—' }}</td>
                <td class="r">{{ $currency }}{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td style="color:#555;">Subtotal</td>
                <td class="r">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if ($invoice->discount_amount > 0)
            <tr>
                <td style="color:#555;">Discount</td>
                <td class="r" style="color:#991b1b;">- {{ $currency }}{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if ($invoice->scholarship_amount > 0)
            <tr>
                <td style="color:#555;">Scholarship</td>
                <td class="r" style="color:#991b1b;">- {{ $currency }}{{ number_format($invoice->scholarship_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand">
                <td>Total Due</td>
                <td class="r">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="color:#065f46;">Amount Paid</td>
                <td class="r" style="color:#065f46;">{{ $currency }}{{ number_format($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr class="balance {{ $invoice->balance <= 0 ? 'paid' : '' }}">
                <td>Balance</td>
                <td class="r">{{ $currency }}{{ number_format($invoice->balance, 2) }}</td>
            </tr>
        </table>
    </div>

    @if ($invoice->notes)
    <div class="notes"><strong>Notes:</strong> {{ $invoice->notes }}</div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-row">
            <div class="footer-left">
                Generated {{ now()->format('d M Y H:i') }} &bull; {{ $school->name }}
                @if ($invoice->createdBy) &bull; Prepared by {{ $invoice->createdBy->name }} @endif
            </div>
            <div class="footer-right">This is a computer-generated invoice. No signature required.</div>
        </div>
    </div>

</div>
</body>
</html>
