<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    const TYPES = [
        'purchase'   => ['label' => 'Purchase',   'sign' => '+', 'color' => 'text-success'],
        'issue'      => ['label' => 'Issue',       'sign' => '-', 'color' => 'text-danger'],
        'return'     => ['label' => 'Return',      'sign' => '+', 'color' => 'text-success'],
        'adjustment' => ['label' => 'Adjustment',  'sign' => '±', 'color' => 'text-muted'],
        'damage'     => ['label' => 'Damage/Loss', 'sign' => '-', 'color' => 'text-danger'],
    ];

    protected $fillable = [
        'school_id', 'inventory_item_id', 'supplier_id',
        'movement_type', 'quantity', 'balance_after', 'unit_cost',
        'reference', 'issued_to_type', 'issued_to_id', 'notes',
        'recorded_by', 'movement_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity'      => 'integer',
            'balance_after' => 'integer',
            'unit_cost'     => 'float',
            'movement_date' => 'datetime',
        ];
    }

    public function item(): BelongsTo       { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function supplier(): BelongsTo   { return $this->belongsTo(Supplier::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getTypeLabelAttribute(): string { return self::TYPES[$this->movement_type]['label'] ?? ucfirst($this->movement_type); }
    public function getTypeSignAttribute(): string  { return self::TYPES[$this->movement_type]['sign'] ?? ''; }
    public function getTypeColorAttribute(): string { return self::TYPES[$this->movement_type]['color'] ?? 'text-muted'; }
}
