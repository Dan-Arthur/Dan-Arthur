<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    protected $fillable = ['school_id', 'name', 'bio'];

    public function school(): BelongsTo    { return $this->belongsTo(School::class); }
    public function books(): BelongsToMany { return $this->belongsToMany(Book::class, 'book_authors'); }
}
