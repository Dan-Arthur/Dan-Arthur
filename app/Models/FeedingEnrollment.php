<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingEnrollment extends Model
{
    const STATUSES = [
        'active'    => ['label' => 'Active',    'color' => 'badge-success'],
        'suspended' => ['label' => 'Suspended', 'color' => 'badge-warning'],
        'withdrawn' => ['label' => 'Withdrawn', 'color' => 'badge-gray'],
    ];

    protected $fillable = [
        'school_id', 'student_id', 'academic_year_id',
        'enrolled_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['enrolled_date' => 'date'];
    }

    public function school(): BelongsTo        { return $this->belongsTo(School::class); }
    public function student(): BelongsTo       { return $this->belongsTo(Student::class); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }
}
