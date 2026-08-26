<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructureItem extends Model
{
    protected $fillable = [
        'fee_structure_id', 'fee_category_id', 'name', 'amount', 'is_mandatory', 'notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['amount' => 'float', 'is_mandatory' => 'boolean', 'sort_order' => 'integer'];
    }

    public function feeStructure(): BelongsTo  { return $this->belongsTo(FeeStructure::class); }
    public function feeCategory(): BelongsTo   { return $this->belongsTo(FeeCategory::class); }
}
