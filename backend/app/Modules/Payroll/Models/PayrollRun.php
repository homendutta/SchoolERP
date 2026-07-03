<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\PayrollRunStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A payroll run for a period. Immutable once Locked; corrections need a new run. */
class PayrollRun extends Model
{
    protected $table = 'payroll_runs';

    protected $fillable = [
        'school_id', 'run_number', 'label', 'period_year', 'period_month', 'status',
        'total_earnings', 'total_deductions', 'total_employer', 'total_net',
        'processed_count', 'locked_at', 'locked_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_employer' => 'decimal:2',
            'total_net' => 'decimal:2',
            'processed_count' => 'integer',
            'locked_at' => 'datetime',
            'status' => PayrollRunStatus::class,
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'run_id');
    }

    public function isLocked(): bool
    {
        return $this->status === PayrollRunStatus::Locked;
    }
}
