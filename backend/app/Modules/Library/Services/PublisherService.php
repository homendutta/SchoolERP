<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\Publisher;
use App\Platform\Shared\Services\BaseCrudService;

class PublisherService extends BaseCrudService
{
    protected function model(): string
    {
        return Publisher::class;
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
