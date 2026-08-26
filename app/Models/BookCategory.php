<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCategory extends Model
{
    protected $fillable = ['school_id', 'name', 'code', 'parent_id'];

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function parent(): BelongsTo      { return $this->belongsTo(BookCategory::class, 'parent_id'); }
    public function children(): HasMany      { return $this->hasMany(BookCategory::class, 'parent_id'); }
    public function books(): HasMany         { return $this->hasMany(Book::class, 'category_id'); }
}
