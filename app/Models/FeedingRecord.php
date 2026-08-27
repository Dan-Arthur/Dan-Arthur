<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingRecord extends Model
{
    protected $fillable = [
        'school_id', 'student_id', 'record_date', 'fed', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'fed'         => 'boolean',
        ];
    }

    public function school(): BelongsTo   { return $this->belongsTo(School::class); }
    public function student(): BelongsTo  { return $this->belongsTo(Student::class); }
}
