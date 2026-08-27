<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    const TYPES = [
        'percentage' => 'Percentage (%)',
        'fixed'      => 'Fixed Amount',
    ];

    protected $fillable = ['school_id', 'name', 'type', 'value', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['value' => 'float', 'is_active' => 'boolean'];
    }

    public function school(): BelongsTo              { return $this->belongsTo(School::class); }
    public function studentScholarships(): HasMany   { return $this->hasMany(StudentScholarship::class); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getValueDisplayAttribute(): string
    {
        return $this->type === 'percentage'
            ? number_format($this->value, 1) . '%'
            : (auth()->user()?->school->currency_symbol ?? '₵') . number_format($this->value, 2);
    }
}
