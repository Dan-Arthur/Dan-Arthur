<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookCopy extends Model
{
    const CONDITIONS = ['good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'damaged' => 'Damaged', 'lost' => 'Lost'];
    const STATUSES   = [
        'available' => ['label' => 'Available', 'color' => 'badge-success'],
        'loaned'    => ['label' => 'Loaned',    'color' => 'badge-warning'],
        'reserved'  => ['label' => 'Reserved',  'color' => 'badge-gray'],
        'lost'      => ['label' => 'Lost',      'color' => 'badge-danger'],
        'damaged'   => ['label' => 'Damaged',   'color' => 'badge-danger'],
    ];

    protected $fillable = ['book_id', 'barcode', 'accession_number', 'condition', 'status'];

    public function book(): BelongsTo        { return $this->belongsTo(Book::class); }
    public function loans(): HasMany         { return $this->hasMany(BookLoan::class); }
    public function activeLoan(): HasOne     { return $this->hasOne(BookLoan::class)->where('status', 'active'); }
}
