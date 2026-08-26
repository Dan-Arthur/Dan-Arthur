<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrolment extends Model
{
    const STATUSES = [
        'active'      => ['label' => 'Active',      'color' => 'badge-success'],
        'withdrawn'   => ['label' => 'Withdrawn',   'color' => 'badge-danger'],
        'transferred' => ['label' => 'Transferred', 'color' => 'badge-warning'],
        'graduated'   => ['label' => 'Graduated',   'color' => 'badge-purple'],
    ];

    protected $fillable = [
        'student_id', 'class_id', 'academic_year_id', 'term_id',
        'roll_number', 'status', 'enrolled_date', 'exit_date', 'exit_reason', 'is_promoted',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_date' => 'date',
            'exit_date'     => 'date',
            'is_promoted'   => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
