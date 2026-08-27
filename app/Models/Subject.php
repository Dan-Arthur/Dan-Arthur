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

    const LEVELS = [
        'early_childhood' => 'Creche / Nursery',
        'kindergarten'    => 'Kindergarten (KG 1–2)',
        'primary'         => 'Primary (P1–P6)',
        'jhs'             => 'Junior High School (JHS 1–3)',
        'shs'             => 'Senior High School (SHS 1–3)',
    ];

    const CATEGORIES = [
        'language'        => 'Language',
        'mathematics'     => 'Mathematics',
        'science'         => 'Science',
        'social_studies'  => 'Social Studies',
        'arts'            => 'Creative Arts',
        'religious'       => 'Religious Studies',
        'physical_edu'    => 'Physical Education',
        'technology'      => 'Technology',
        'business'        => 'Business',
        'home_economics'  => 'Home Economics',
        'visual_arts'     => 'Visual Arts',
        'performing_arts' => 'Performing Arts',
        'technical'       => 'Technical / Engineering',
        'vocational'      => 'Vocational',
        'early_childhood' => 'Early Childhood Dev.',
        'other'           => 'Other',
    ];

    protected $fillable = [
        'school_id', 'department_id', 'name', 'code', 'type',
        'category', 'level', 'credit_hours', 'has_practical', 'is_active',
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

    public function getLevelLabelAttribute(): string
    {
        return self::LEVELS[$this->level] ?? 'Universal';
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
