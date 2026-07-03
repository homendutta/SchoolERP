<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\LeaveApprovalAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One append-only decision in a leave request's multi-level approval trail. */
class LeaveApproval extends Model
{
    protected $table = 'hr_leave_approvals';

    protected $fillable = ['leave_request_id', 'level', 'approver_id', 'action', 'notes', 'acted_at'];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'acted_at' => 'datetime',
            'action' => LeaveApprovalAction::class,
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }
}
