<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use SoftDeletes;

    const TYPES = [
        'assignment' => 'Assignment',
        'class_test' => 'Class Test',
        'quiz'       => 'Quiz',
        'ca'         => 'Continuous Assessment',
        'mid_term'   => 'Mid-Term Exam',
        'exam'       => 'End of Term Exam',
        'mock'       => 'Mock Exam',
    ];

    const STATUSES = [
        'draft'       => ['label' => 'Draft',       'color' => 'badge-gray'],
        'published'   => ['label' => 'Published',   'color' => 'badge-info'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'badge-warning'],
        'completed'   => ['label' => 'Completed',   'color' => 'badge-success'],
        'cancelled'   => ['label' => 'Cancelled',   'color' => 'badge-danger'],
    ];

    protected $fillable = [
        'school_id', 'class_id', 'subject_id', 'teacher_id', 'academic_year_id', 'term_id',
        'title', 'type', 'max_score', 'weight', 'assessment_date', 'submission_deadline',
        'description', 'status', 'marks_entered',
    ];

    protected function casts(): array
    {
        return [
            'max_score'           => 'float',
            'weight'              => 'float',
            'assessment_date'     => 'date',
            'submission_deadline' => 'date',
            'marks_entered'       => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
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
