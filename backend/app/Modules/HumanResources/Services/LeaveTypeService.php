<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\LeaveType;
use App\Platform\Shared\Services\BaseCrudService;

/** Configurable leave types (nothing hardcoded). */
class LeaveTypeService extends BaseCrudService
{
    protected function model(): string
    {
        return LeaveType::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'is_paid'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
