<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\Shift;
use App\Platform\Shared\Services\BaseCrudService;

/** Configurable work shifts. */
class ShiftService extends BaseCrudService
{
    protected function model(): string
    {
        return Shift::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
