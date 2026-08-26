<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    const TYPES = [
        'internal' => 'Internal',
        'email'    => 'Email',
        'sms'      => 'SMS',
    ];

    protected $fillable = [
        'school_id', 'sender_id', 'subject', 'body', 'type', 'status',
    ];

    public function school(): BelongsTo        { return $this->belongsTo(School::class); }
    public function sender(): BelongsTo        { return $this->belongsTo(User::class, 'sender_id'); }
    public function recipients(): HasMany      { return $this->hasMany(MessageRecipient::class); }
    public function recipientUsers(): HasMany  { return $this->hasMany(MessageRecipient::class)->with('user'); }
}
