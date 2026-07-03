<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Enums\StatutoryType;
use App\Modules\Payroll\Models\StatutoryComponent;
use App\Platform\Shared\Services\BaseCrudService;

/** Configurable statutory components (PF / ESI / PT / TDS / Other). Config only. */
class StatutoryService extends BaseCrudService
{
    protected function model(): string
    {
        return StatutoryComponent::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'statutory_type', 'status'];
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
        return [
            'statutory_type' => ['type' => 'enum', 'enum' => StatutoryType::class],
        ];
    }
}
