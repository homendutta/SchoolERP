<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\CategoryType;
use App\Modules\Cms\Models\Category;
use App\Platform\Shared\Services\BaseCrudService;

class CategoryService extends BaseCrudService
{
    protected function model(): string
    {
        return Category::class;
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['type' => ['type' => 'enum', 'enum' => CategoryType::class]];
    }
}
