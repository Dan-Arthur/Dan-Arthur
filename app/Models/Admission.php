<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use HasFactory, SoftDeletes;

    const STATUSES = [
        'draft'        => ['label' => 'Draft',        'color' => 'gray'],
        'submitted'    => ['label' => 'Submitted',    'color' => 'blue'],
        'under_review' => ['label' => 'Under Review', 'color' => 'yellow'],
        'interview'    => ['label' => 'Interview',    'color' => 'purple'],
        'entrance_exam'=> ['label' => 'Entrance Exam','color' => 'indigo'],
        'accepted'     => ['label' => 'Accepted',     'color' => 'green'],
        'rejected'     => ['label' => 'Rejected',     'color' => 'red'],
        'waitlisted'   => ['label' => 'Waitlisted',   'color' => 'orange'],
        'enrolled'     => ['label' => 'Enrolled',     'color' => 'teal'],
    ];

    protected $fillable = [
        'school_id', 'academic_year_id', 'campus_id', 'student_id',
        'application_number', 'first_name', 'last_name', 'other_names',
        'gender', 'date_of_birth', 'nationality', 'religion',
        'address', 'email', 'phone', 'previous_school', 'applying_for_class',
        'applied_class_id', 'status', 'application_date', 'interview_date',
        'exam_date', 'decision_date', 'decision_notes', 'reviewed_by',
        'decided_by', 'guardian_info', 'documents', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'   => 'date',
            'application_date'=> 'date',
            'interview_date'  => 'date',
            'exam_date'       => 'date',
            'decision_date'   => 'date',
            'guardian_info'   => 'array',
            'documents'       => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function appliedClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'applied_class_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }

    public function canAdvanceTo(string $newStatus): bool
    {
        $flow = [
            'draft'        => ['submitted'],
            'submitted'    => ['under_review', 'rejected'],
            'under_review' => ['interview', 'entrance_exam', 'accepted', 'rejected', 'waitlisted'],
            'interview'    => ['entrance_exam', 'accepted', 'rejected', 'waitlisted'],
            'entrance_exam'=> ['accepted', 'rejected', 'waitlisted'],
            'accepted'     => ['enrolled', 'rejected'],
            'waitlisted'   => ['accepted', 'rejected'],
        ];

        return in_array($newStatus, $flow[$this->status] ?? []);
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
