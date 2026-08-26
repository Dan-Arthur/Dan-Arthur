<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'school_classes';

    protected $fillable = [
        'school_id', 'campus_id', 'department_id', 'name', 'code',
        'level', 'section', 'programme', 'capacity', 'class_teacher_id',
        'room', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'level'     => 'integer',
            'capacity'  => 'integer',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'current_class_id');
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class, 'class_id');
    }

    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        if ($this->section) {
            $parts[] = $this->section;
        }
        return implode(' ', $parts);
    }

    public function getEnrolledCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getOccupancyPercentAttribute(): int
    {
        if ($this->capacity <= 0) {
            return 0;
        }
        return min(100, (int) round(($this->enrolled_count / $this->capacity) * 100));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
