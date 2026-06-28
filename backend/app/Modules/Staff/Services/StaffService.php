<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Staff\Enums\StaffStatus;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Staff lifecycle service. Staff are created ONLY here (Staff Management owns
 * creation); employee numbers come from the Number Generator. Maintains the
 * employee with a timeline + audit trail.
 */
class StaffService extends BaseCrudService
{
    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly StaffTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    protected function model(): string
    {
        return Staff::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'department:id,label,value',
            'designation:id,label,value',
            'gender:id,label,value',
            'bloodGroup:id,label,value',
            'reportingManager:id,name,employee_number',
        ]);
    }

    protected function searchable(): array
    {
        return ['name', 'employee_number', 'phone', 'email'];
    }

    protected function filterable(): array
    {
        return ['status', 'school_id', 'department_id', 'designation_id', 'is_teaching', 'employment_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'employee_number', 'joining_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'employee_number' => ['type' => 'text', 'columns' => ['employee_number']],
            'name' => ['type' => 'text', 'columns' => ['name']],
            'phone' => ['type' => 'text', 'columns' => ['phone']],
            'email' => ['type' => 'text', 'columns' => ['email']],
            'status' => ['type' => 'enum', 'enum' => StaffStatus::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Staff {
            $schoolId = $data['school_id'] ?? null;
            $manualNumber = ! empty($data['employee_number']);
            $data['employee_number'] ??= $this->numbers->next('employee_number', $schoolId);
            $data['status'] ??= StaffStatus::Active->value;

            /** @var Staff $staff */
            $staff = Staff::query()->create($data);

            // An admin-supplied number is reserved so future auto numbers never collide.
            if ($manualNumber) {
                $this->numbers->reserve('employee_number', (string) $staff->employee_number, $schoolId);
            }

            $this->timeline->record($staff, TimelineEvent::Created, 'Staff record created');
            $this->activity->record('staff.created', "Created staff {$staff->name} ({$staff->employee_number})", $staff, [], $staff->school_id, 'staff');

            return $staff;
        });
    }

    /** Update the profile; records a department-change or profile-update event. */
    public function updateProfile(Staff $staff, array $data): Staff
    {
        $departmentChanged = array_key_exists('department_id', $data)
            && (int) $data['department_id'] !== (int) $staff->department_id;
        $numberChanged = array_key_exists('employee_number', $data)
            && (string) $data['employee_number'] !== (string) $staff->employee_number;
        $oldNumber = (string) $staff->employee_number;

        /** @var Staff $updated */
        $updated = $this->update($staff, $data);

        // Preserve Employee Number integrity: keep the sequence ahead of any
        // manually-edited number and record the change in the registry + audit.
        if ($numberChanged) {
            $this->numbers->reserve('employee_number', (string) $updated->employee_number, $updated->school_id);
            $this->activity->record('staff.employee_number_changed', "Employee number {$oldNumber} → {$updated->employee_number}", $updated, [
                'from' => $oldNumber, 'to' => $updated->employee_number,
            ], $updated->school_id, 'staff');
            $this->timeline->record($updated, TimelineEvent::ProfileUpdated, "Employee number changed to {$updated->employee_number}");
        }

        if ($departmentChanged) {
            $this->timeline->record($updated, TimelineEvent::DepartmentChanged, 'Department changed');
        } elseif (! $numberChanged) {
            $this->timeline->record($updated, TimelineEvent::ProfileUpdated, 'Profile updated');
        }
        $this->activity->record('staff.profile_updated', "Updated staff {$updated->name}", $updated, [], $updated->school_id, 'staff');

        return $updated;
    }

    public function profile(int|string $id): Model
    {
        return Staff::query()
            ->with([
                'department:id,label,value',
                'designation:id,label,value',
                'gender:id,label,value',
                'bloodGroup:id,label,value',
                'reportingManager:id,name,employee_number',
                'qualifications.certificate:id,uuid',
                'experiences.certificate:id,uuid',
                'documents.documentType:id,label,value',
            ])
            ->findOrFail($id);
    }
}
