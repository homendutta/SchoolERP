<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\AdjustmentType;
use App\Modules\Finance\Models\Adjustment;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class AdjustmentReadService extends BaseCrudService
{
    protected function model(): string
    {
        return Adjustment::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['student:id,name,admission_number']);
    }

    protected function searchable(): array
    {
        return ['transaction_number', 'reason'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at', 'amount'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['type' => ['type' => 'enum', 'enum' => AdjustmentType::class]];
    }
}
