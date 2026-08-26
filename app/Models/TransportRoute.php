<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    const DIRECTIONS = ['pickup' => 'Pickup', 'dropoff' => 'Dropoff', 'both' => 'Both'];

    protected $fillable = [
        'school_id', 'vehicle_id', 'driver_id', 'name', 'code', 'direction', 'monthly_fee', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'monthly_fee' => 'float'];
    }

    public function school(): BelongsTo   { return $this->belongsTo(School::class); }
    public function vehicle(): BelongsTo  { return $this->belongsTo(Vehicle::class); }
    public function driver(): BelongsTo   { return $this->belongsTo(Driver::class); }
    public function stops(): HasMany      { return $this->hasMany(TransportStop::class, 'route_id')->orderBy('sequence'); }
    public function students(): HasMany   { return $this->hasMany(StudentTransport::class, 'route_id'); }
}
