<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    const STATUSES = [
        'draft'            => ['label' => 'Draft',            'color' => 'badge-gray'],
        'pending_approval' => ['label' => 'Pending Approval', 'color' => 'badge-warning'],
        'approved'         => ['label' => 'Approved',         'color' => 'badge-info'],
        'published'        => ['label' => 'Published',        'color' => 'badge-success'],
        'locked'           => ['label' => 'Locked',           'color' => 'badge-purple'],
    ];

    protected $fillable = [
        'school_id', 'student_id', 'class_id', 'academic_year_id', 'term_id',
        'total_score', 'average_score', 'position', 'class_size', 'subjects_offered',
        'gpa', 'overall_grade', 'overall_remark', 'class_teacher_comment', 'principal_comment',
        'status', 'approved_by', 'approved_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'total_score'   => 'float',
            'average_score' => 'float',
            'gpa'           => 'float',
            'approved_at'   => 'datetime',
            'published_at'  => 'datetime',
        ];
    }

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function student(): BelongsTo   { return $this->belongsTo(Student::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function term(): BelongsTo      { return $this->belongsTo(Term::class); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function subjectScores(): HasMany
    {
        return $this->hasMany(ResultSubjectScore::class)->orderBy('id');
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
