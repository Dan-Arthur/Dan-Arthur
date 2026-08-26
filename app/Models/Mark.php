<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    protected $fillable = [
        'assessment_id', 'student_id', 'score', 'is_absent', 'is_exempt', 'remarks', 'entered_by', 'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'score'      => 'float',
            'is_absent'  => 'boolean',
            'is_exempt'  => 'boolean',
            'entered_at' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function getPercentageAttribute(): ?float
    {
        if ($this->is_absent || $this->is_exempt || is_null($this->score)) {
            return null;
        }
        $max = $this->assessment?->max_score ?? 100;
        return $max > 0 ? round($this->score / $max * 100, 1) : null;
    }
}
