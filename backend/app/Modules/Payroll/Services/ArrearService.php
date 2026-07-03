<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Enums\ArrearType;
use App\Modules\Payroll\Models\Arrear;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Arrears (salary / adjustment), applied during payroll processing. */
class ArrearService extends BaseCrudService
{
    protected function model(): string
    {
        return Arrear::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'arrear_type', 'applied'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'arrear_type' => ['type' => 'enum', 'enum' => ArrearType::class],
        ];
    }
}
