<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    const TYPES    = ['bus' => 'Bus', 'van' => 'Van', 'car' => 'Car'];
    const STATUSES = [
        'active'      => ['label' => 'Active',      'color' => 'badge-success'],
        'inactive'    => ['label' => 'Inactive',    'color' => 'badge-gray'],
        'maintenance' => ['label' => 'Maintenance', 'color' => 'badge-warning'],
    ];

    protected $fillable = [
        'school_id', 'registration_number', 'make', 'model', 'year', 'color',
        'capacity', 'type', 'status',
        'insurance_expiry', 'road_worthiness_expiry', 'last_service_date', 'next_service_date',
    ];

    protected function casts(): array
    {
        return [
            'capacity'               => 'integer',
            'insurance_expiry'       => 'date',
            'road_worthiness_expiry' => 'date',
            'last_service_date'      => 'date',
            'next_service_date'      => 'date',
        ];
    }

    public function school(): BelongsTo  { return $this->belongsTo(School::class); }
    public function routes(): HasMany    { return $this->hasMany(TransportRoute::class); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'badge-gray'; }

    public function getInsuranceExpiredAttribute(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }
}
