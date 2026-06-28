<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Scholarship;
use App\Platform\Shared\Services\BaseCrudService;

class ScholarshipService extends BaseCrudService
{
    protected function model(): string
    {
        return Scholarship::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'type', 'method', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
