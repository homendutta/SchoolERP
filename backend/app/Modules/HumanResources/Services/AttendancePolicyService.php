<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\AttendancePolicy;
use App\Platform\Shared\Services\BaseCrudService;

/** Attendance policies defined by HR and consumed by the Attendance module. */
class AttendancePolicyService extends BaseCrudService
{
    protected function model(): string
    {
        return AttendancePolicy::class;
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'overtime_eligible'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
