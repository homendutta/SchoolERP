<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Consumable;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Consumables — stock items, never given an Identity. */
class ConsumableService extends BaseCrudService
{
    protected function model(): string
    {
        return Consumable::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['category:id,name']);
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'current_stock'];
    }
}
