<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    const STATUSES = [
        'draft'    => ['label' => 'Draft',    'color' => 'badge-gray'],
        'approved' => ['label' => 'Approved', 'color' => 'badge-blue'],
        'paid'     => ['label' => 'Paid',     'color' => 'badge-green'],
    ];

    const MONTHS = [
        1=>'January',2=>'February',3=>'March',4=>'April',
        5=>'May',6=>'June',7=>'July',8=>'August',
        9=>'September',10=>'October',11=>'November',12=>'December',
    ];

    protected $fillable = [
        'school_id','run_by','title','month','year','status',
        'total_gross','total_deductions','total_net','notes','approved_at','paid_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
        'total_gross'       => 'float',
        'total_deductions'  => 'float',
        'total_net'         => 'float',
    ];

    public function school(): BelongsTo  { return $this->belongsTo(School::class); }
    public function runner(): BelongsTo  { return $this->belongsTo(User::class, 'run_by'); }
    public function payslips(): HasMany  { return $this->hasMany(Payslip::class, 'payroll_run_id'); }

    public function recalculate(): void
    {
        $totals = $this->payslips()->selectRaw(
            'SUM(gross_pay) as gross, SUM(total_deductions) as deductions, SUM(net_pay) as net'
        )->first();

        $this->update([
            'total_gross'       => $totals->gross ?? 0,
            'total_deductions'  => $totals->deductions ?? 0,
            'total_net'         => $totals->net ?? 0,
        ]);
    }

    public function getPeriodLabelAttribute(): string
    {
        return (self::MONTHS[$this->month] ?? '') . ' ' . $this->year;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'badge-gray';
    }
}
