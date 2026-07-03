<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\HolidayType;
use App\Modules\HumanResources\Models\Holiday;
use App\Platform\Shared\Services\BaseCrudService;

/** Configurable holidays. Academic Calendar may reference these. */
class HolidayService extends BaseCrudService
{
    protected function model(): string
    {
        return Holiday::class;
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'holiday_type', 'is_optional', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'date'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'holiday_type' => ['type' => 'enum', 'enum' => HolidayType::class],
            'date' => ['type' => 'date', 'columns' => ['date']],
        ];
    }
}
