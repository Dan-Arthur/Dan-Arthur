<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    const STATUSES = [
        'active'     => ['label' => 'Active',     'color' => 'badge-success'],
        'inactive'   => ['label' => 'Inactive',   'color' => 'badge-gray'],
        'resigned'   => ['label' => 'Resigned',   'color' => 'badge-warning'],
        'terminated' => ['label' => 'Terminated', 'color' => 'badge-danger'],
        'retired'    => ['label' => 'Retired',    'color' => 'badge-gray'],
    ];

    const EMPLOYMENT_TYPES = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract'  => 'Contract',
    ];

    const TITLES = ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Rev'];

    protected $fillable = [
        'school_id', 'campus_id', 'user_id', 'position_id', 'department_id',
        'employee_number', 'title', 'first_name', 'last_name', 'other_names',
        'gender', 'date_of_birth', 'nationality', 'national_id',
        'phone', 'alt_phone', 'email', 'address', 'photo',
        'qualification', 'specialisation', 'years_experience',
        'joining_date', 'employment_type', 'status',
        'exit_date', 'exit_reason',
        'basic_salary', 'bank_name', 'bank_account', 'bank_sort_code',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'   => 'date',
            'joining_date'    => 'date',
            'exit_date'       => 'date',
            'basic_salary'    => 'float',
            'years_experience'=> 'integer',
        ];
    }

    public function school(): BelongsTo     { return $this->belongsTo(School::class); }
    public function campus(): BelongsTo     { return $this->belongsTo(Campus::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function position(): BelongsTo  { return $this->belongsTo(Position::class); }
    public function department(): BelongsTo{ return $this->belongsTo(Department::class); }
    public function leaveRequests(): HasMany{ return $this->hasMany(LeaveRequest::class); }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->title, $this->first_name, $this->last_name]);
        return implode(' ', $parts);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getYearsOfServiceAttribute(): ?int
    {
        return $this->joining_date?->diffInYears(now());
    }
}
