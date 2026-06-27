<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\MasterDataType;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class MasterDataTypeService extends BaseCrudService
{
    protected function model(): string
    {
        return MasterDataType::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('group:id,name,slug')->withCount('values');
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function filterable(): array
    {
        return ['group_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'slug', 'sort_order', 'created_at'];
    }

    protected function searchDefinitions(): array
    {
        return [
            'name' => ['type' => 'text', 'columns' => ['name', 'slug', 'description']],
            'group' => ['type' => 'relation', 'relation' => 'group', 'columns' => ['name', 'slug']],
            'created_at' => ['type' => 'date'],
        ];
    }
}
