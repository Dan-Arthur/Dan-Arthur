<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsAlert extends Model
{
    const GROUPS = [
        'all_parents'     => 'All Parents / Guardians',
        'all_staff'       => 'All Staff',
        'all_students'    => 'All Students',
        'class_parents'   => 'Parents of a Class',
        'class_students'  => 'Students of a Class',
    ];

    protected $fillable = [
        'school_id', 'sender_id', 'body', 'recipient_group',
        'class_id', 'recipients_count', 'phone_numbers', 'status', 'sent_at',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'sent_at'       => 'datetime',
    ];

    public function school(): BelongsTo  { return $this->belongsTo(School::class); }
    public function sender(): BelongsTo  { return $this->belongsTo(User::class, 'sender_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }

    public function groupLabel(): string
    {
        return self::GROUPS[$this->recipient_group] ?? $this->recipient_group;
    }
}
