<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    const TYPES = [
        'core'             => ['label' => 'Core',             'color' => 'badge-info'],
        'elective'         => ['label' => 'Elective',         'color' => 'badge-purple'],
        'extra_curricular' => ['label' => 'Extra-Curricular', 'color' => 'badge-success'],
    ];

    const CATEGORIES = [
        'science'       => 'Science',
        'arts'          => 'Arts',
        'language'      => 'Language',
        'mathematics'   => 'Mathematics',
        'social_studies'=> 'Social Studies',
        'vocational'    => 'Vocational',
        'technology'    => 'Technology',
        'physical_edu'  => 'Physical Education',
        'religious'     => 'Religious Studies',
        'other'         => 'Other',
    ];

    protected $fillable = [
        'school_id', 'department_id', 'name', 'code', 'type',
        'category', 'credit_hours', 'has_practical', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'has_practical' => 'boolean',
            'is_active'     => 'boolean',
            'credit_hours'  => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subjects', 'subject_id', 'class_id')
            ->withPivot('academic_year_id', 'teacher_id', 'is_compulsory', 'periods_per_week')
            ->withTimestamps();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'badge-gray';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category ?? ''));
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
