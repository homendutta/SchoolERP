<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\LeaveApprovalAction;
use App\Modules\HumanResources\Enums\LeaveStatus;
use App\Modules\HumanResources\Models\LeaveApproval;
use App\Modules\HumanResources\Models\LeaveBalance;
use App\Modules\HumanResources\Models\LeavePolicy;
use App\Modules\HumanResources\Models\LeaveRequest;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * The Leave Engine — the SINGLE path through which leave is processed: apply,
 * approve (multi-level), reject, cancel. It tracks the balance, records the
 * append-only approval trail, and writes the Timeline, the Audit Log and a
 * Communication event on every decision. No leave state changes out of band.
 */
class LeaveEngine extends BaseService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly HrHooks $hooks,
    ) {}

    /**
     * Apply for leave. Computes the number of days, resolves the policy's
     * approval levels, ensures a balance row exists and records the request.
     *
     * @param  array<string, mixed>  $data
     */
    public function apply(array $data): LeaveRequest
    {
        return $this->transaction(function () use ($data): LeaveRequest {
            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);

            if ($end->lt($start)) {
                throw BusinessRuleException::make('End date cannot be before the start date.', 'INVALID_LEAVE_RANGE');
            }

            $days = (float) ($data['days'] ?? ($start->diffInDays($end) + 1));
            $policy = $this->resolvePolicy($data);
            $levels = (int) ($policy?->approval_levels ?? 1);

            $request = LeaveRequest::query()->create([
                'school_id' => $data['school_id'],
                'staff_id' => $data['staff_id'],
                'leave_type_id' => $data['leave_type_id'],
                'leave_policy_id' => $policy?->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days' => $days,
                'reason' => $data['reason'] ?? null,
                'status' => LeaveStatus::Pending->value,
                'approval_levels' => max(1, $levels),
                'current_level' => 0,
                'applied_on' => Carbon::now()->toDateString(),
            ]);

            $this->ensureBalance($request, $policy);

            $this->timeline->record((int) $request->staff_id, 'hr.leave_applied', 'Leave applied', $request->reason, [
                'leave_request_id' => $request->id, 'days' => $days,
            ]);
            $this->activity->record('hr.leave_applied', 'Leave applied', $request, [
                'staff_id' => $request->staff_id, 'days' => $days,
            ], (int) $request->school_id, 'hr');

            return $request->refresh();
        });
    }

    /** Approve one level; when the final level is reached the balance is deducted. */
    public function approve(LeaveRequest $request, ?string $notes = null): LeaveRequest
    {
        return $this->transaction(function () use ($request, $notes): LeaveRequest {
            $this->guardPending($request);

            $level = (int) $request->current_level + 1;
            $this->recordApproval($request, $level, LeaveApprovalAction::Approved, $notes);
            $request->current_level = $level;

            if ($level >= (int) $request->approval_levels) {
                $this->deductBalance($request);
                $request->status = LeaveStatus::Approved->value;
                $request->decided_by = Auth::id();
                $request->decided_on = Carbon::now()->toDateString();
                $request->decision_notes = $notes;
                $request->save();

                $this->timeline->record((int) $request->staff_id, 'hr.leave_approved', 'Leave approved', $notes, ['leave_request_id' => $request->id]);
                $this->activity->record('hr.leave_approved', 'Leave approved', $request, ['days' => $request->days], (int) $request->school_id, 'hr');
                $this->hooks->leaveApproved((int) $request->school_id, "Leave request #{$request->id} approved.");
            } else {
                $request->save();
                $this->activity->record('hr.leave_level_approved', "Leave approved at level {$level}", $request, ['level' => $level], (int) $request->school_id, 'hr');
            }

            return $request->refresh();
        });
    }

    /** Reject the request (records the decision and notifies through the engine). */
    public function reject(LeaveRequest $request, ?string $notes = null): LeaveRequest
    {
        return $this->transaction(function () use ($request, $notes): LeaveRequest {
            $this->guardPending($request);

            $level = (int) $request->current_level + 1;
            $this->recordApproval($request, $level, LeaveApprovalAction::Rejected, $notes);

            $request->status = LeaveStatus::Rejected->value;
            $request->decided_by = Auth::id();
            $request->decided_on = Carbon::now()->toDateString();
            $request->decision_notes = $notes;
            $request->save();

            $this->timeline->record((int) $request->staff_id, 'hr.leave_rejected', 'Leave rejected', $notes, ['leave_request_id' => $request->id]);
            $this->activity->record('hr.leave_rejected', 'Leave rejected', $request, [], (int) $request->school_id, 'hr');
            $this->hooks->leaveRejected((int) $request->school_id, "Leave request #{$request->id} rejected.");

            return $request->refresh();
        });
    }

    /** Cancel a request; if it was approved the deducted balance is refunded. */
    public function cancel(LeaveRequest $request): LeaveRequest
    {
        return $this->transaction(function () use ($request): LeaveRequest {
            if ($request->status === LeaveStatus::Cancelled) {
                throw BusinessRuleException::make('This leave request is already cancelled.', 'LEAVE_ALREADY_CANCELLED');
            }

            if ($request->status === LeaveStatus::Approved) {
                $this->refundBalance($request);
            }

            $request->status = LeaveStatus::Cancelled->value;
            $request->save();

            $this->timeline->record((int) $request->staff_id, 'hr.leave_cancelled', 'Leave cancelled', null, ['leave_request_id' => $request->id]);
            $this->activity->record('hr.leave_cancelled', 'Leave cancelled', $request, [], (int) $request->school_id, 'hr');

            return $request->refresh();
        });
    }

    private function guardPending(LeaveRequest $request): void
    {
        if ($request->status !== LeaveStatus::Pending) {
            throw BusinessRuleException::make('Only a pending leave request can be decided.', 'LEAVE_NOT_PENDING');
        }
    }

    private function recordApproval(LeaveRequest $request, int $level, LeaveApprovalAction $action, ?string $notes): void
    {
        LeaveApproval::query()->create([
            'leave_request_id' => $request->id,
            'level' => $level,
            'approver_id' => Auth::id(),
            'action' => $action->value,
            'notes' => $notes,
            'acted_at' => Carbon::now(),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function resolvePolicy(array $data): ?LeavePolicy
    {
        if (! empty($data['leave_policy_id'])) {
            return LeavePolicy::query()->find($data['leave_policy_id']);
        }

        return LeavePolicy::query()
            ->where('leave_type_id', $data['leave_type_id'])
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    private function ensureBalance(LeaveRequest $request, ?LeavePolicy $policy): LeaveBalance
    {
        $year = Carbon::parse($request->start_date)->year;

        return LeaveBalance::query()->firstOrCreate(
            ['staff_id' => $request->staff_id, 'leave_type_id' => $request->leave_type_id, 'year' => $year],
            ['school_id' => $request->school_id, 'allocated' => (float) ($policy?->annual_allocation ?? 0), 'carried_forward' => 0, 'used' => 0],
        );
    }

    private function deductBalance(LeaveRequest $request): void
    {
        $balance = $this->ensureBalance($request, null);
        $policy = $request->leave_policy_id !== null ? LeavePolicy::query()->find($request->leave_policy_id) : null;

        $available = (float) $balance->allocated + (float) $balance->carried_forward - (float) $balance->used;
        if (! ($policy?->negative_balance_allowed ?? false) && $available < (float) $request->days) {
            throw BusinessRuleException::make('Insufficient leave balance for this request.', 'INSUFFICIENT_LEAVE_BALANCE');
        }

        $balance->used = (float) $balance->used + (float) $request->days;
        $balance->save();
    }

    private function refundBalance(LeaveRequest $request): void
    {
        $balance = $this->ensureBalance($request, null);
        $balance->used = max(0, (float) $balance->used - (float) $request->days);
        $balance->save();
    }
}
