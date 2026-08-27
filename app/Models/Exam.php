<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    const STATUSES = [
        'scheduled' => ['label' => 'Scheduled', 'color' => 'badge-blue'],
        'ongoing'   => ['label' => 'Ongoing',   'color' => 'badge-yellow'],
        'completed' => ['label' => 'Completed', 'color' => 'badge-green'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'badge-gray'],
    ];

    protected $fillable = [
        'school_id', 'academic_year_id', 'term_id', 'school_class_id', 'subject_id',
        'title', 'exam_date', 'start_time', 'duration_minutes', 'venue', 'invigilator',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'exam_date'        => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function term(): BelongsTo        { return $this->belongsTo(Term::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function subject(): BelongsTo     { return $this->belongsTo(Subject::class); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function getEndTimeAttribute(): ?string
    {
        if (!$this->start_time || !$this->duration_minutes) {
            return null;
        }
        return \Carbon\Carbon::createFromFormat('H:i:s', $this->start_time)
            ->addMinutes($this->duration_minutes)
            ->format('H:i');
    }
}
