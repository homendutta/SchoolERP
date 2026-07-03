<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Enums\CalculationType;
use App\Modules\Payroll\Enums\ComponentType;
use App\Modules\Payroll\Models\Component;
use App\Platform\Shared\Services\BaseCrudService;

/** Configurable salary components. */
class ComponentService extends BaseCrudService
{
    protected function model(): string
    {
        return Component::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'component_type', 'calculation_type', 'status'];
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
            'component_type' => ['type' => 'enum', 'enum' => ComponentType::class],
            'calculation_type' => ['type' => 'enum', 'enum' => CalculationType::class],
        ];
    }
}
