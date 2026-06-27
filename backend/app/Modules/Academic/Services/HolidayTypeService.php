<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\HolidayType;
use App\Platform\Shared\Services\BaseCrudService;

class HolidayTypeService extends BaseCrudService
{
    protected function model(): string
    {
        return HolidayType::class;
    }

    protected function searchable(): array
    {
        return ['name', 'slug'];
    }

    protected function filterable(): array
    {
        return ['status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
