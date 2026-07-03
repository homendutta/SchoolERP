<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-employee, per-type, per-year leave balance. `balance` is derived
 * (allocated + carried_forward - used). Future Payroll consumes these balances.
 */
class LeaveBalance extends Model
{
    protected $table = 'hr_leave_balances';

    protected $fillable = ['school_id', 'staff_id', 'leave_type_id', 'year', 'allocated', 'carried_forward', 'used'];

    protected $appends = ['balance'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'allocated' => 'decimal:2',
            'carried_forward' => 'decimal:2',
            'used' => 'decimal:2',
        ];
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->allocated + (float) $this->carried_forward - (float) $this->used;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
