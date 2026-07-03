<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\MenuLocation;
use App\Modules\Cms\Models\Menu;
use App\Platform\Shared\Services\BaseCrudService;

class MenuService extends BaseCrudService
{
    protected function model(): string
    {
        return Menu::class;
    }

    protected function searchable(): array
    {
        return ['label'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'location', 'parent_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'sequence'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['location' => ['type' => 'enum', 'enum' => MenuLocation::class]];
    }
}
