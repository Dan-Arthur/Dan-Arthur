<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    const CATEGORIES = [
        'utilities'       => 'Utilities',
        'stationery'      => 'Stationery & Supplies',
        'maintenance'     => 'Maintenance & Repairs',
        'staff_welfare'   => 'Staff Welfare',
        'transport'       => 'Transport & Travel',
        'rent'            => 'Rent & Rates',
        'it'              => 'IT & Technology',
        'catering'        => 'Catering & Food',
        'examination'     => 'Examination',
        'marketing'       => 'Marketing & Outreach',
        'miscellaneous'   => 'Miscellaneous',
    ];

    const PAYMENT_METHODS = [
        'cash'           => 'Cash',
        'bank_transfer'  => 'Bank Transfer',
        'cheque'         => 'Cheque',
        'mobile_money'   => 'Mobile Money',
    ];

    const STATUSES = [
        'pending'  => ['label' => 'Pending',  'color' => 'badge-yellow'],
        'approved' => ['label' => 'Approved', 'color' => 'badge-green'],
        'rejected' => ['label' => 'Rejected', 'color' => 'badge-red'],
    ];

    protected $fillable = [
        'school_id', 'category', 'title', 'amount', 'date',
        'paid_to', 'payment_method', 'reference_number',
        'status', 'recorded_by', 'approved_by', 'approved_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'amount'      => 'float',
            'approved_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function recordedBy(): BelongsTo  { return $this->belongsTo(User::class, 'recorded_by'); }
    public function approvedBy(): BelongsTo  { return $this->belongsTo(User::class, 'approved_by'); }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
