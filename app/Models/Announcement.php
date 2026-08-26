<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    const TYPES = [
        'general'   => 'General',
        'academic'  => 'Academic',
        'event'     => 'Event',
        'emergency' => 'Emergency',
        'fee'       => 'Fee',
    ];

    const AUDIENCES = [
        'all'      => 'Everyone',
        'students' => 'Students',
        'parents'  => 'Parents / Guardians',
        'staff'    => 'All Staff',
        'teachers' => 'Teachers Only',
    ];

    const STATUSES = [
        'draft'     => ['label' => 'Draft',     'color' => 'badge-gray'],
        'published' => ['label' => 'Published', 'color' => 'badge-success'],
        'archived'  => ['label' => 'Archived',  'color' => 'badge-warning'],
    ];

    protected $fillable = [
        'school_id', 'created_by', 'title', 'body', 'type', 'audience',
        'audience_filter', 'is_pinned', 'publish_at', 'expires_at', 'status', 'attachment',
    ];

    protected function casts(): array
    {
        return [
            'audience_filter' => 'array',
            'is_pinned'       => 'boolean',
            'publish_at'      => 'datetime',
            'expires_at'      => 'datetime',
        ];
    }

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function author(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'badge-gray'; }
    public function getIsExpiredAttribute(): bool     { return $this->expires_at && $this->expires_at->isPast(); }
    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'published'
            && ($this->publish_at === null || $this->publish_at->isPast())
            && !$this->is_expired;
    }
}
