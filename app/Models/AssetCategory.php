<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    protected $fillable = ['school_id', 'name', 'code', 'parent_id'];

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function parent(): BelongsTo    { return $this->belongsTo(AssetCategory::class, 'parent_id'); }
    public function children(): HasMany    { return $this->hasMany(AssetCategory::class, 'parent_id'); }
    public function assets(): HasMany      { return $this->hasMany(Asset::class, 'category_id'); }
    public function inventoryItems(): HasMany { return $this->hasMany(InventoryItem::class, 'category_id'); }
}
