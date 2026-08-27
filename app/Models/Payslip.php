<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'school_id','payroll_run_id','employee_id',
        'basic_salary','allowances','deductions',
        'gross_pay','total_deductions','net_pay','status','notes',
    ];

    protected $casts = [
        'allowances'      => 'array',
        'deductions'      => 'array',
        'basic_salary'    => 'float',
        'gross_pay'       => 'float',
        'total_deductions'=> 'float',
        'net_pay'         => 'float',
    ];

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function payrollRun(): BelongsTo  { return $this->belongsTo(PayrollRun::class, 'payroll_run_id'); }
    public function employee(): BelongsTo    { return $this->belongsTo(Employee::class); }

    public function recalculate(): void
    {
        $allowTotal = array_sum($this->allowances ?? []);
        $deductTotal = array_sum($this->deductions ?? []);
        $gross = $this->basic_salary + $allowTotal;
        $net   = max(0, $gross - $deductTotal);

        $this->update([
            'gross_pay'       => $gross,
            'total_deductions'=> $deductTotal,
            'net_pay'         => $net,
        ]);
    }
}
