<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    const UNITS = ['piece' => 'Piece', 'box' => 'Box', 'ream' => 'Ream', 'litre' => 'Litre', 'kg' => 'Kg', 'set' => 'Set'];

    protected $fillable = [
        'school_id', 'category_id', 'name', 'code', 'unit',
        'quantity_in_stock', 'reorder_level', 'unit_cost', 'location', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in_stock' => 'integer',
            'reorder_level'     => 'integer',
            'unit_cost'         => 'float',
            'is_active'         => 'boolean',
        ];
    }

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function category(): BelongsTo  { return $this->belongsTo(AssetCategory::class); }
    public function movements(): HasMany   { return $this->hasMany(StockMovement::class); }

    public function getBelowReorderAttribute(): bool
    {
        return $this->quantity_in_stock <= $this->reorder_level;
    }
}
