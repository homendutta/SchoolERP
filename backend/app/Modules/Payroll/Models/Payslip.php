<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\SettlementStatus;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A generated payslip (financial document). Payslip number from the Number Generator. */
class Payslip extends Model
{
    protected $table = 'payroll_payslips';

    protected $fillable = [
        'school_id', 'run_id', 'staff_id', 'assignment_id', 'payslip_number',
        'period_year', 'period_month', 'gross_earnings', 'total_deductions',
        'employer_contributions', 'net_pay', 'present_days', 'absent_days',
        'leave_days', 'lwp_days', 'overtime_hours', 'overtime_amount', 'settlement_status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['settlement_status' => 'unpaid'];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'gross_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'employer_contributions' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'present_days' => 'decimal:2',
            'absent_days' => 'decimal:2',
            'leave_days' => 'decimal:2',
            'lwp_days' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'settlement_status' => SettlementStatus::class,
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayslipLine::class, 'payslip_id');
    }
}
