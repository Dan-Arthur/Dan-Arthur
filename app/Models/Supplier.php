<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['school_id', 'name', 'contact_person', 'phone', 'email', 'address', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function school(): BelongsTo       { return $this->belongsTo(School::class); }
    public function assets(): HasMany         { return $this->hasMany(Asset::class); }
    public function stockMovements(): HasMany { return $this->hasMany(StockMovement::class); }
}
