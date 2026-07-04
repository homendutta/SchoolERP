<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Models\Category;
use App\Platform\Shared\Services\BaseCrudService;

class CategoryService extends BaseCrudService
{
    protected function model(): string
    {
        return Category::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
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
