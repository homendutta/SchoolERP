<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Models\Building;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class BuildingService extends BaseCrudService
{
    protected function model(): string
    {
        return Building::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['hostel:id,name'])->withCount('floors');
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'hostel_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
