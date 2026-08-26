<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    protected $table = 'student_attendance';

    const STATUSES = [
        'present' => ['label' => 'Present',  'color' => 'badge-success'],
        'absent'  => ['label' => 'Absent',   'color' => 'badge-danger'],
        'late'    => ['label' => 'Late',     'color' => 'badge-warning'],
        'excused' => ['label' => 'Excused',  'color' => 'badge-info'],
    ];

    protected $fillable = [
        'school_id', 'student_id', 'class_id', 'academic_year_id', 'term_id',
        'date', 'status', 'arrival_time', 'reason', 'taken_by', 'method',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }
}
