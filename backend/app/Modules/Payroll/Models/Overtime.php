<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An overtime entry. Payroll only calculates APPROVED overtime. */
class Overtime extends Model
{
    protected $table = 'payroll_overtime';

    protected $fillable = [
        'school_id', 'staff_id', 'period_year', 'period_month', 'hours', 'hourly_rate',
        'max_hours', 'eligible', 'approved', 'approved_by', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'approved' => false, 'eligible' => true];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'max_hours' => 'decimal:2',
            'eligible' => 'boolean',
            'approved' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /** Effective payable hours, capped at the configured maximum. */
    public function payableHours(): float
    {
        $hours = (float) $this->hours;
        if ($this->max_hours !== null) {
            $hours = min($hours, (float) $this->max_hours);
        }

        return $hours;
    }
}
