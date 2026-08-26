<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRecipient extends Model
{
    protected $fillable = [
        'message_id', 'recipient_id', 'is_read', 'read_at', 'is_starred', 'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'is_read'    => 'boolean',
            'is_starred' => 'boolean',
            'is_deleted' => 'boolean',
            'read_at'    => 'datetime',
        ];
    }

    public function message(): BelongsTo { return $this->belongsTo(Message::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class, 'recipient_id'); }
}
