<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    protected $fillable = ['school_id', 'name', 'contact'];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function books(): HasMany    { return $this->hasMany(Book::class); }
}
