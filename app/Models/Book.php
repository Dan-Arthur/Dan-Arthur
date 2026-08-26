<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'school_id', 'category_id', 'publisher_id',
        'title', 'isbn', 'edition', 'publish_year', 'language',
        'total_copies', 'available_copies', 'location', 'cover_image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'total_copies'     => 'integer',
            'available_copies' => 'integer',
        ];
    }

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function category(): BelongsTo    { return $this->belongsTo(BookCategory::class); }
    public function publisher(): BelongsTo   { return $this->belongsTo(Publisher::class); }
    public function copies(): HasMany        { return $this->hasMany(BookCopy::class); }
    public function loans(): HasMany         { return $this->hasMany(BookLoan::class, 'book_copy_id'); }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors');
    }

    public function getAuthorNamesAttribute(): string
    {
        return $this->authors->pluck('name')->join(', ') ?: '—';
    }
}
