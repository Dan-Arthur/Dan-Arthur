<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    const STATUSES = [
        'unpaid'    => ['label' => 'Unpaid',    'color' => 'badge-danger'],
        'partial'   => ['label' => 'Partial',   'color' => 'badge-warning'],
        'paid'      => ['label' => 'Paid',      'color' => 'badge-success'],
        'overdue'   => ['label' => 'Overdue',   'color' => 'badge-danger'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'badge-gray'],
    ];

    const METHODS = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
        'online'        => 'Online',
        'pos'           => 'POS / Card',
    ];

    protected $fillable = [
        'school_id', 'student_id', 'academic_year_id', 'term_id', 'fee_structure_id',
        'invoice_number', 'issue_date', 'due_date', 'subtotal', 'discount_amount',
        'scholarship_amount', 'total_amount', 'amount_paid', 'balance', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'         => 'date',
            'due_date'           => 'date',
            'subtotal'           => 'float',
            'discount_amount'    => 'float',
            'scholarship_amount' => 'float',
            'total_amount'       => 'float',
            'amount_paid'        => 'float',
            'balance'            => 'float',
        ];
    }

    public function school(): BelongsTo        { return $this->belongsTo(School::class); }
    public function student(): BelongsTo       { return $this->belongsTo(Student::class); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }
    public function term(): BelongsTo          { return $this->belongsTo(Term::class); }
    public function feeStructure(): BelongsTo  { return $this->belongsTo(FeeStructure::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }

    public function items(): HasMany    { return $this->hasMany(InvoiceItem::class)->orderBy('id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class)->orderByDesc('payment_date'); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function recalculate(): void
    {
        $paid = $this->payments()->where('status', 'confirmed')->sum('amount');
        $balance = max(0, $this->total_amount - $paid);

        $status = 'unpaid';
        if ($this->status === 'cancelled') {
            $status = 'cancelled';
        } elseif ($paid >= $this->total_amount) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $status = 'overdue';
        }

        $this->update(['amount_paid' => $paid, 'balance' => $balance, 'status' => $status]);
    }

    public static function nextNumber(int $schoolId): string
    {
        $year = date('Y');
        $last = static::where('school_id', $schoolId)
            ->whereYear('created_at', $year)
            ->count();
        return 'INV-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
