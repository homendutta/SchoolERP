<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Enums\LoanStatus;
use App\Modules\Payroll\Models\Loan;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Employee loans & advances. Payroll deducts installments while active; Finance
 * owns the actual cash movement (never handled here). Approving a loan publishes
 * a Communication event.
 */
class LoanService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly PayrollHooks $hooks,
    ) {}

    protected function model(): string
    {
        return Loan::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'loan_type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            // Balance starts at the principal unless explicitly provided.
            $data['balance'] = $data['balance'] ?? ($data['principal'] ?? 0);

            return Loan::query()->create($data);
        });
    }

    /** Approve a loan (activates installment deductions + notifies via the engine). */
    public function approve(Loan $loan): Loan
    {
        return $this->transaction(function () use ($loan): Loan {
            $loan->update(['status' => LoanStatus::Active->value, 'approved_by' => Auth::id()]);

            $this->timeline->record((int) $loan->staff_id, 'payroll.loan_approved', 'Loan approved', $loan->reference, [
                'loan_id' => $loan->id, 'principal' => $loan->principal,
            ]);
            $this->activity->record('payroll.loan_approved', 'Loan approved', $loan, [
                'loan_type' => $loan->loan_type->value,
            ], (int) $loan->school_id, 'payroll');
            $this->hooks->loanApproved((int) $loan->school_id, "Loan approved for employee #{$loan->staff_id}.");

            return $loan->refresh();
        });
    }
}
