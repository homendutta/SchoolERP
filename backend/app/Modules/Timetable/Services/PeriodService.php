<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Models\TimetablePeriod;
use App\Platform\Shared\Services\BaseCrudService;

class PeriodService extends BaseCrudService
{
    protected function model(): string
    {
        return TimetablePeriod::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'is_break'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'name', 'start_time', 'created_at'];
    }
}
