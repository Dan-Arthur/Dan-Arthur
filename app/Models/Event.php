<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    const TYPES = [
        'general'     => 'General',
        'academic'    => 'Academic',
        'examination' => 'Examination',
        'holiday'     => 'Holiday',
        'sport'       => 'Sport',
        'meeting'     => 'Meeting',
        'other'       => 'Other',
    ];

    const AUDIENCES = [
        'all'      => 'Everyone',
        'students' => 'Students',
        'parents'  => 'Parents / Guardians',
        'staff'    => 'All Staff',
        'teachers' => 'Teachers Only',
    ];

    const STATUSES = [
        'scheduled'   => ['label' => 'Scheduled',   'color' => 'badge-primary'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'badge-warning'],
        'completed'   => ['label' => 'Completed',   'color' => 'badge-success'],
        'cancelled'   => ['label' => 'Cancelled',   'color' => 'badge-danger'],
    ];

    protected $fillable = [
        'school_id', 'created_by', 'title', 'description', 'type', 'audience',
        'start_datetime', 'end_datetime', 'all_day', 'location', 'status', 'color',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime'   => 'datetime',
            'all_day'        => 'boolean',
        ];
    }

    public function school(): BelongsTo  { return $this->belongsTo(School::class); }
    public function author(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'badge-gray'; }
    public function getIsPastAttribute(): bool        { return $this->start_datetime->isPast(); }
    public function getDurationAttribute(): string
    {
        if (!$this->end_datetime) return $this->start_datetime->format('d M Y');
        if ($this->all_day) return $this->start_datetime->format('d M Y') . ' – ' . $this->end_datetime->format('d M Y');
        return $this->start_datetime->format('d M Y H:i') . ' – ' . $this->end_datetime->format('H:i');
    }
}
