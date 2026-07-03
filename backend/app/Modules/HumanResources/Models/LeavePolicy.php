<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Leave POLICY — separate from leave type. Defines annual allocation, carry
 * forward, encashment, negative balance and the number of approval levels.
 * Future Payroll consumes the resulting leave balances.
 */
class LeavePolicy extends Model
{
    use SoftDeletes;

    protected $table = 'hr_leave_policies';

    protected $fillable = [
        'school_id', 'leave_type_id', 'name', 'annual_allocation', 'carry_forward',
        'carry_forward_limit', 'encashment_allowed', 'negative_balance_allowed',
        'approval_levels', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'annual_allocation' => 'decimal:2',
            'carry_forward' => 'boolean',
            'carry_forward_limit' => 'decimal:2',
            'encashment_allowed' => 'boolean',
            'negative_balance_allowed' => 'boolean',
            'approval_levels' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
