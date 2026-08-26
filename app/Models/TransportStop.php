<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportStop extends Model
{
    protected $fillable = ['route_id', 'name', 'address', 'pickup_time', 'dropoff_time', 'sequence'];
    protected function casts(): array { return ['sequence' => 'integer']; }

    public function route(): BelongsTo { return $this->belongsTo(TransportRoute::class, 'route_id'); }
}
