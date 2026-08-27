<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';

    const STATUSES = [
        'present'  => ['label' => 'Present',  'color' => 'badge-green'],
        'absent'   => ['label' => 'Absent',   'color' => 'badge-red'],
        'late'     => ['label' => 'Late',     'color' => 'badge-yellow'],
        'half_day' => ['label' => 'Half Day', 'color' => 'badge-blue'],
        'on_leave' => ['label' => 'On Leave', 'color' => 'badge-gray'],
    ];

    protected $fillable = [
        'school_id', 'user_id', 'date', 'status',
        'check_in', 'check_out', 'reason', 'method',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function school(): BelongsTo   { return $this->belongsTo(School::class); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }
}
