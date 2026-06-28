<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FeeCategory;
use App\Platform\Shared\Services\BaseCrudService;

class FeeCategoryService extends BaseCrudService
{
    protected function model(): string
    {
        return FeeCategory::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'is_active', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'name'];
    }
}
