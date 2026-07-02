<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Models\Stop;
use App\Platform\Shared\Services\BaseCrudService;

class StopService extends BaseCrudService
{
    protected function model(): string
    {
        return Stop::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'route_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'sequence', 'name'];
    }
}
