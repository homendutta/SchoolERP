<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\LibraryLocation;
use App\Platform\Shared\Services\BaseCrudService;

class LocationService extends BaseCrudService
{
    protected function model(): string
    {
        return LibraryLocation::class;
    }

    protected function searchable(): array
    {
        return ['name', 'room', 'rack', 'shelf'];
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
