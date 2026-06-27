<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class MasterDataValueService extends BaseCrudService
{
    protected function model(): string
    {
        return MasterDataValue::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('type:id,name,slug');
    }

    protected function searchable(): array
    {
        return ['label', 'value', 'description'];
    }

    protected function filterable(): array
    {
        return ['type_id', 'is_active'];
    }

    protected function sortable(): array
    {
        return ['id', 'label', 'value', 'sort_order', 'created_at'];
    }

    protected function searchDefinitions(): array
    {
        return [
            'label' => ['type' => 'text', 'columns' => ['label', 'value', 'description']],
            'is_active' => ['type' => 'numeric'],
            'sort_order' => ['type' => 'numeric'],
            'created_at' => ['type' => 'date'],
            'type' => ['type' => 'relation', 'relation' => 'type', 'columns' => ['name', 'slug']],
        ];
    }
}
