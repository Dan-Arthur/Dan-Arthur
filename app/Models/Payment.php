<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    const STATUSES = [
        'pending'   => ['label' => 'Pending',   'color' => 'badge-warning'],
        'confirmed' => ['label' => 'Confirmed', 'color' => 'badge-success'],
        'failed'    => ['label' => 'Failed',    'color' => 'badge-danger'],
        'reversed'  => ['label' => 'Reversed',  'color' => 'badge-gray'],
    ];

    protected $fillable = [
        'school_id', 'invoice_id', 'student_id', 'payment_number', 'amount',
        'payment_date', 'payment_method', 'reference_number', 'bank_name',
        'status', 'notes', 'received_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'float', 'payment_date' => 'date'];
    }

    public function invoice(): BelongsTo    { return $this->belongsTo(Invoice::class); }
    public function student(): BelongsTo    { return $this->belongsTo(Student::class); }
    public function receivedBy(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function receipt(): HasOne       { return $this->hasOne(Receipt::class); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function getMethodLabelAttribute(): string
    {
        return Invoice::METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public static function nextNumber(int $schoolId): string
    {
        $year = date('Y');
        $last = static::where('school_id', $schoolId)
            ->whereYear('created_at', $year)
            ->count();
        return 'PAY-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
