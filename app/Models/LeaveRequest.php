<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    const STATUSES = [
        'pending'   => ['label' => 'Pending',   'color' => 'badge-warning'],
        'approved'  => ['label' => 'Approved',  'color' => 'badge-success'],
        'rejected'  => ['label' => 'Rejected',  'color' => 'badge-danger'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'badge-gray'],
    ];

    protected $fillable = [
        'school_id', 'employee_id', 'leave_type_id', 'approved_by',
        'start_date', 'end_date', 'days_requested',
        'reason', 'status', 'actioned_at', 'action_note',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'actioned_at'  => 'datetime',
            'days_requested' => 'integer',
        ];
    }

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function approvedBy(): BelongsTo{ return $this->belongsTo(User::class, 'approved_by'); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }
}
