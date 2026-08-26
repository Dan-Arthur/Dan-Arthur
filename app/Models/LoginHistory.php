<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    public $timestamps = false;

    protected $table = 'login_history';

    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'device', 'browser',
        'platform', 'successful', 'failure_reason', 'logged_in_at', 'logged_out_at',
    ];

    protected function casts(): array
    {
        return [
            'successful'   => 'boolean',
            'logged_in_at' => 'datetime',
            'logged_out_at'=> 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
