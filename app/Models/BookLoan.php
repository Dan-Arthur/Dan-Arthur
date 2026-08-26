<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookLoan extends Model
{
    const STATUSES = [
        'active'   => ['label' => 'Active',   'color' => 'badge-success'],
        'returned' => ['label' => 'Returned', 'color' => 'badge-gray'],
        'overdue'  => ['label' => 'Overdue',  'color' => 'badge-danger'],
        'lost'     => ['label' => 'Lost',     'color' => 'badge-danger'],
    ];

    protected $fillable = [
        'school_id', 'book_copy_id', 'member_type', 'member_id',
        'loan_date', 'due_date', 'return_date', 'status',
        'fine_amount', 'fine_paid', 'issued_by', 'returned_to', 'return_condition',
    ];

    protected function casts(): array
    {
        return [
            'loan_date'   => 'date',
            'due_date'    => 'date',
            'return_date' => 'date',
            'fine_amount' => 'float',
            'fine_paid'   => 'float',
        ];
    }

    public function bookCopy(): BelongsTo  { return $this->belongsTo(BookCopy::class); }
    public function issuedBy(): BelongsTo  { return $this->belongsTo(User::class, 'issued_by'); }
    public function returnedTo(): BelongsTo{ return $this->belongsTo(User::class, 'returned_to'); }

    public function getMemberAttribute(): ?Model
    {
        return match ($this->member_type) {
            'student'  => Student::find($this->member_id),
            'employee' => Employee::find($this->member_id),
            default    => null,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active' && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) return 0;
        return $this->due_date->diffInDays(now());
    }
}
