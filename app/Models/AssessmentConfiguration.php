<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentConfiguration extends Model
{
    protected $table = 'assessment_configurations';

    protected $fillable = [
        'school_id', 'class_id', 'academic_year_id', 'term_id',
        'name', 'components', 'total_score', 'grading_scale_id', 'show_position', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'components'   => 'array',
            'total_score'  => 'float',
            'show_position'=> 'boolean',
            'is_default'   => 'boolean',
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

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }
}
