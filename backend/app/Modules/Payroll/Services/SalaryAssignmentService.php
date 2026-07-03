<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\SalaryAssignment;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Employee salary assignments. Each create is a NEW immutable version; the
 * previous current version is closed (is_current = false). History is never
 * overwritten. Every assignment writes to the Staff Timeline and the Audit Log.
 */
class SalaryAssignmentService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
    ) {}

    protected function model(): string
    {
        return SalaryAssignment::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'employee:id,name,employee_number',
            'structure:id,name,grade',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'structure_id', 'is_current'];
    }

    protected function sortable(): array
    {
        return ['id', 'effective_date', 'revision_number'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            return $this->assign($data);
        });
    }

    /**
     * Create a new current salary version for an employee, closing the previous one.
     *
     * @param  array<string, mixed>  $data
     */
    public function assign(array $data): SalaryAssignment
    {
        $previous = SalaryAssignment::query()
            ->where('staff_id', $data['staff_id'])
            ->where('is_current', true)
            ->latest('revision_number')
            ->first();

        SalaryAssignment::query()
            ->where('staff_id', $data['staff_id'])
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $data['revision_number'] = (int) ($previous?->revision_number ?? 0) + 1;
        $data['is_current'] = true;
        $assignment = SalaryAssignment::query()->create($data);

        $this->timeline->record((int) $assignment->staff_id, 'payroll.salary_assigned', 'Salary structure assigned', $assignment->reason, [
            'assignment_id' => $assignment->id, 'revision_number' => $assignment->revision_number,
        ]);
        $this->activity->record('payroll.salary_assigned', 'Salary structure assigned', $assignment, [
            'structure_id' => $assignment->structure_id, 'revision_number' => $assignment->revision_number,
        ], (int) $assignment->school_id, 'payroll');

        return $assignment;
    }
}
