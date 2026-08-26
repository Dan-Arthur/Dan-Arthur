<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    const CONDITIONS = ['good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'damaged' => 'Damaged', 'disposed' => 'Disposed'];
    const STATUSES   = [
        'active'      => ['label' => 'Active',      'color' => 'badge-success'],
        'maintenance' => ['label' => 'Maintenance', 'color' => 'badge-warning'],
        'disposed'    => ['label' => 'Disposed',    'color' => 'badge-gray'],
        'lost'        => ['label' => 'Lost',        'color' => 'badge-danger'],
    ];

    protected $fillable = [
        'school_id', 'category_id', 'supplier_id',
        'name', 'asset_tag', 'serial_number', 'brand', 'model',
        'purchase_price', 'purchase_date', 'warranty_expiry',
        'location', 'assigned_to_type', 'assigned_to_id',
        'condition', 'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price'  => 'float',
            'purchase_date'   => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function category(): BelongsTo  { return $this->belongsTo(AssetCategory::class); }
    public function supplier(): BelongsTo  { return $this->belongsTo(Supplier::class); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'badge-gray'; }

    public function getWarrantyExpiredAttribute(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isPast();
    }
}
