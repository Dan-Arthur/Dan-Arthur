<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingScale extends Model
{
    protected $fillable = ['school_id', 'name', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function bands(): HasMany
    {
        return $this->hasMany(GradeBand::class)->orderByDesc('min_score');
    }

    public function gradeFor(float $score): ?GradeBand
    {
        return $this->bands->first(fn($b) => $score >= $b->min_score && $score <= $b->max_score);
    }
}
