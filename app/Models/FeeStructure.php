<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    protected $fillable = [
        'school_id', 'academic_year_id', 'term_id', 'class_id', 'campus_id',
        'name', 'applies_to_all_classes', 'student_category', 'is_active',
    ];

    protected function casts(): array
    {
        return ['applies_to_all_classes' => 'boolean', 'is_active' => 'boolean'];
    }

    public function school(): BelongsTo        { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo  { return $this->belongsTo(AcademicYear::class); }
    public function term(): BelongsTo          { return $this->belongsTo(Term::class); }
    public function schoolClass(): BelongsTo   { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function campus(): BelongsTo        { return $this->belongsTo(Campus::class); }

    public function items(): HasMany
    {
        return $this->hasMany(FeeStructureItem::class)->orderBy('sort_order');
    }

    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }

    public function getTotalAttribute(): float
    {
        return $this->items->sum('amount');
    }
}
