<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisciplinaryRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'student_id', 'reported_by', 'academic_year_id', 'term_id',
        'category', 'severity', 'incident_date', 'location', 'description',
        'action_taken', 'follow_up_date', 'follow_up_notes',
        'parent_notified', 'parent_notified_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'incident_date'       => 'date',
            'follow_up_date'      => 'date',
            'parent_notified'     => 'boolean',
            'parent_notified_at'  => 'datetime',
        ];
    }

    const CATEGORIES = [
        'bullying'        => 'Bullying',
        'fighting'        => 'Fighting',
        'theft'           => 'Theft',
        'vandalism'       => 'Vandalism',
        'cheating'        => 'Cheating / Academic Dishonesty',
        'truancy'         => 'Truancy / Absenteeism',
        'misconduct'      => 'General Misconduct',
        'disrespect'      => 'Disrespect to Staff',
        'harassment'      => 'Harassment',
        'substance_abuse' => 'Substance Abuse',
        'other'           => 'Other',
    ];

    const SEVERITIES = [
        'minor'    => ['label' => 'Minor',    'color' => 'badge-yellow'],
        'moderate' => ['label' => 'Moderate', 'color' => 'badge-orange'],
        'major'    => ['label' => 'Major',    'color' => 'badge-red'],
    ];

    const STATUSES = [
        'open'           => ['label' => 'Open',           'color' => 'badge-red'],
        'pending_review' => ['label' => 'Pending Review', 'color' => 'badge-yellow'],
        'resolved'       => ['label' => 'Resolved',       'color' => 'badge-green'],
    ];

    public function school(): BelongsTo        { return $this->belongsTo(School::class); }
    public function student(): BelongsTo       { return $this->belongsTo(Student::class); }
    public function reportedBy(): BelongsTo    { return $this->belongsTo(User::class, 'reported_by'); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }
    public function term(): BelongsTo          { return $this->belongsTo(Term::class); }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category ?? 'Other');
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::SEVERITIES[$this->severity]['label'] ?? ucfirst($this->severity);
    }

    public function getSeverityColorAttribute(): string
    {
        return self::SEVERITIES[$this->severity]['color'] ?? 'badge-gray';
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
