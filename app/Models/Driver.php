<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'school_id', 'employee_id', 'licence_number', 'licence_class', 'licence_expiry', 'status',
    ];

    protected function casts(): array
    {
        return ['licence_expiry' => 'date'];
    }

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function routes(): HasMany      { return $this->hasMany(TransportRoute::class); }

    public function getNameAttribute(): string
    {
        return $this->employee?->full_name ?? 'Driver #' . $this->id;
    }

    public function getLicenceExpiredAttribute(): bool
    {
        return $this->licence_expiry && $this->licence_expiry->isPast();
    }
}
