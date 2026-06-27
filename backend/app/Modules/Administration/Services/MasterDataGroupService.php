<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\MasterDataGroup;
use App\Platform\Shared\Services\BaseCrudService;

class MasterDataGroupService extends BaseCrudService
{
    protected function model(): string
    {
        return MasterDataGroup::class;
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'slug', 'sort_order', 'created_at'];
    }
}
