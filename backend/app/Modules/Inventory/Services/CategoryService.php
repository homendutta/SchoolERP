<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\AssetCategory;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class CategoryService extends BaseCrudService
{
    protected function model(): string
    {
        return AssetCategory::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['parent:id,name']);
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'parent_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
