<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'school_id', 'name', 'days_allowed', 'is_paid', 'requires_approval',
    ];

    protected function casts(): array
    {
        return [
            'days_allowed'      => 'integer',
            'is_paid'           => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function requests(): HasMany    { return $this->hasMany(LeaveRequest::class); }
}
