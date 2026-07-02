<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\AssetModel;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class ModelService extends BaseCrudService
{
    protected function model(): string
    {
        return AssetModel::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['category:id,name']);
    }

    protected function searchable(): array
    {
        return ['name', 'brand', 'model_number'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
