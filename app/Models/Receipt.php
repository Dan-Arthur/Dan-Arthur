<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = ['payment_id', 'receipt_number', 'issued_at', 'issued_by', 'file_path'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    public function payment(): BelongsTo  { return $this->belongsTo(Payment::class); }
    public function issuedBy(): BelongsTo { return $this->belongsTo(User::class, 'issued_by'); }

    public static function nextNumber(int $schoolId): string
    {
        $year = date('Y');
        $last = static::whereHas('payment', fn($q) => $q->where('school_id', $schoolId))
            ->whereYear('issued_at', $year)
            ->count();
        return 'RCP-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
