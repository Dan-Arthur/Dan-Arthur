<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    const TYPES = [
        'teaching'     => 'Teaching',
        'non_teaching' => 'Non-Teaching',
        'management'   => 'Management',
    ];

    protected $fillable = [
        'school_id', 'department_id', 'title', 'code', 'type', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function school(): BelongsTo     { return $this->belongsTo(School::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function employees(): HasMany    { return $this->hasMany(Employee::class); }
}
