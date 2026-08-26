<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetablePeriod extends Model
{
    protected $fillable = [
        'school_id', 'name', 'start_time', 'end_time', 'is_break', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_break'   => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'period_id');
    }

    public function getDurationAttribute(): string
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);
        $mins  = $start->diffInMinutes($end);
        return $mins . ' min';
    }
}
