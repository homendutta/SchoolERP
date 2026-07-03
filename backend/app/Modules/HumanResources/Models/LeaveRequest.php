<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\LeaveStatus;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A leave request. Processed ONLY through the Leave Engine. Multi-level approval
 * is tracked in an append-only approval trail; the request is never overwritten
 * out of band.
 */
class LeaveRequest extends Model
{
    use SoftDeletes;

    protected $table = 'hr_leave_requests';

    protected $fillable = [
        'school_id', 'staff_id', 'leave_type_id', 'leave_policy_id', 'start_date', 'end_date',
        'days', 'reason', 'status', 'approval_levels', 'current_level', 'applied_on',
        'decided_by', 'decided_on', 'decision_notes',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'current_level' => 0];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'applied_on' => 'date',
            'decided_on' => 'date',
            'days' => 'decimal:2',
            'approval_levels' => 'integer',
            'current_level' => 'integer',
            'status' => LeaveStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveApproval::class, 'leave_request_id');
    }
}
