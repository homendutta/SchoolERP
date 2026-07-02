<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Models\Floor;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class FloorService extends BaseCrudService
{
    protected function model(): string
    {
        return Floor::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['building:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'building_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'floor_number'];
    }
}
