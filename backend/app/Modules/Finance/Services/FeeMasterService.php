<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\FeeFrequency;
use App\Modules\Finance\Models\FeeMaster;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class FeeMasterService extends BaseCrudService
{
    protected function model(): string
    {
        return FeeMaster::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['category:id,name', 'schoolClass:id,name', 'academicYear:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'fee_category_id', 'academic_year_id', 'class_id', 'frequency', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'amount', 'due_date'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['frequency' => ['type' => 'enum', 'enum' => FeeFrequency::class]];
    }
}
