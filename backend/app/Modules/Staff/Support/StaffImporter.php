<?php

declare(strict_types=1);

namespace App\Modules\Staff\Support;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Staff\Enums\StaffStatus;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Shared\Contracts\Importer;

/**
 * Staff importer for the generic Import framework (Upload → Validate → Preview →
 * Import → Summary).
 */
class StaffImporter implements Importer
{
    public function __construct(private readonly StaffTimelineService $timeline) {}

    public function key(): string
    {
        return 'staff';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<int, string>>
     */
    public function validate(array $rows): array
    {
        $errors = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $rowErrors = [];
            $employeeNumber = trim((string) ($row['employee_number'] ?? ''));

            if ($employeeNumber === '') {
                $rowErrors[] = 'Missing employee_number';
            } elseif (isset($seen[$employeeNumber])) {
                $rowErrors[] = 'Duplicate employee_number in file';
            } elseif (Staff::query()->where('employee_number', $employeeNumber)->exists()) {
                $rowErrors[] = 'Duplicate employee_number (already exists)';
            }
            $seen[$employeeNumber] = true;

            if (trim((string) ($row['name'] ?? '')) === '') {
                $rowErrors[] = 'Missing name';
            }
            if (! empty($row['department_id']) && ! MasterDataValue::query()->whereKey($row['department_id'])->exists()) {
                $rowErrors[] = 'Invalid department';
            }
            if (! empty($row['designation_id']) && ! MasterDataValue::query()->whereKey($row['designation_id'])->exists()) {
                $rowErrors[] = 'Invalid designation';
            }
            if (! empty($row['joining_date']) && strtotime((string) $row['joining_date']) === false) {
                $rowErrors[] = 'Invalid joining_date';
            }

            if ($rowErrors !== []) {
                $errors[$index] = $rowErrors;
            }
        }

        return $errors;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    public function execute(array $rows): array
    {
        $created = 0;
        $skipped = 0;
        $errors = $this->validate($rows);

        foreach ($rows as $index => $row) {
            if (isset($errors[$index])) {
                $skipped++;

                continue;
            }

            $staff = Staff::create([
                'school_id' => $row['school_id'] ?? null,
                'employee_number' => $row['employee_number'],
                'name' => $row['name'],
                'gender_id' => $row['gender_id'] ?? null,
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'department_id' => $row['department_id'] ?? null,
                'designation_id' => $row['designation_id'] ?? null,
                'employment_type' => $row['employment_type'] ?? null,
                'joining_date' => $row['joining_date'] ?? null,
                'is_teaching' => filter_var($row['is_teaching'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'status' => StaffStatus::Active->value,
            ]);

            $this->timeline->record($staff, TimelineEvent::Created, 'Staff imported');
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
