<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scholarship extends Model
{
    protected $fillable = ['school_id', 'name', 'type', 'value', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['value' => 'float', 'is_active' => 'boolean'];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}
