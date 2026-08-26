<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'fee_category_id', 'description', 'unit_price', 'quantity', 'discount', 'total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'quantity'   => 'integer',
            'discount'   => 'float',
            'total'      => 'float',
        ];
    }

    public function invoice(): BelongsTo     { return $this->belongsTo(Invoice::class); }
    public function feeCategory(): BelongsTo { return $this->belongsTo(FeeCategory::class); }
}
