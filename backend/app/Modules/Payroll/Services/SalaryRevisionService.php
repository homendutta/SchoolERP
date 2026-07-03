<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\SalaryAssignment;
use App\Modules\Payroll\Models\SalaryRevision;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Salary revisions (promotion / annual increment / special increment /
 * correction). Every revision creates a NEW immutable salary version (via the
 * assignment service) and records the revision as an audit trail. Previous
 * versions remain immutable. Timeline + Audit + Communication are written.
 */
class SalaryRevisionService extends BaseCrudService
{
    public function __construct(
        private readonly SalaryAssignmentService $assignments,
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly PayrollHooks $hooks,
    ) {}

    protected function model(): string
    {
        return SalaryRevision::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'revision_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'effective_date'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $previous = SalaryAssignment::query()
                ->where('staff_id', $data['staff_id'])
                ->where('is_current', true)
                ->latest('revision_number')
                ->first();

            // A revision creates a NEW salary version (previous stays immutable).
            $assignment = $this->assignments->assign([
                'school_id' => $data['school_id'],
                'staff_id' => $data['staff_id'],
                'structure_id' => $data['structure_id'] ?? $previous?->structure_id,
                'effective_date' => $data['effective_date'] ?? null,
                'revision_type' => $data['revision_type'],
                'reason' => $data['reason'] ?? null,
                'approved_by' => $data['approved_by'] ?? null,
            ]);

            $revision = SalaryRevision::query()->create([
                'school_id' => $data['school_id'],
                'staff_id' => $data['staff_id'],
                'assignment_id' => $assignment->id,
                'previous_assignment_id' => $previous?->id,
                'structure_id' => $assignment->structure_id,
                'revision_type' => $data['revision_type'],
                'effective_date' => $data['effective_date'] ?? null,
                'reason' => $data['reason'] ?? null,
                'approved_by' => $data['approved_by'] ?? null,
            ]);

            $this->timeline->record((int) $revision->staff_id, 'payroll.salary_revised', 'Salary revised', $revision->reason, [
                'revision_id' => $revision->id, 'revision_type' => $revision->revision_type->value,
            ]);
            $this->activity->record('payroll.salary_revised', 'Salary revised', $revision, [
                'revision_type' => $revision->revision_type->value,
            ], (int) $revision->school_id, 'payroll');
            $this->hooks->salaryRevision((int) $revision->school_id, "Salary revised for employee #{$revision->staff_id} ({$revision->revision_type->value}).");

            return $revision->refresh();
        });
    }
}
