<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\EmploymentStatus;
use App\Modules\HumanResources\Models\EmploymentRecord;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Employment history engine. An Employee (Staff) is NOT an Employment. Every
 * employment change creates a NEW record and closes the previous current record
 * (is_current = false) — history is never overwritten. Each change writes to the
 * Staff Timeline and the Audit Log.
 */
class EmploymentService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
    ) {}

    protected function model(): string
    {
        return EmploymentRecord::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'department:id,name',
            'designation:id,name',
            'employee:id,name,employee_number',
            'reportingManager:id,name,employee_number',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'department_id', 'designation_id', 'status', 'is_current', 'employment_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'joining_date', 'created_at'];
    }

    /**
     * Record a new employment state. Closes the employee's current record and
     * marks this one current. Used for hire, transfer, promotion, confirmation, etc.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            EmploymentRecord::query()
                ->where('staff_id', $data['staff_id'])
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $data['is_current'] = true;
            $record = EmploymentRecord::query()->create($data);

            $this->timeline->record(
                (int) $record->staff_id,
                'hr.employment_changed',
                'Employment record created',
                $record->change_reason,
                ['employment_id' => $record->id, 'status' => $record->status->value],
            );

            $this->activity->record('hr.employment_changed', 'Employment record created', $record, [
                'staff_id' => $record->staff_id,
                'department_id' => $record->department_id,
                'designation_id' => $record->designation_id,
            ], (int) $record->school_id, 'hr');

            return $record->refresh();
        });
    }

    /**
     * Record a Separated employment state for an employee (used by SeparationService).
     * Never deletes or overwrites prior history.
     */
    public function recordSeparation(int $staffId, int $schoolId, ?string $reason = null): EmploymentRecord
    {
        /** @var EmploymentRecord */
        return $this->create([
            'school_id' => $schoolId,
            'staff_id' => $staffId,
            'status' => EmploymentStatus::Separated->value,
            'change_reason' => $reason ?? 'Employee separation',
        ]);
    }
}
