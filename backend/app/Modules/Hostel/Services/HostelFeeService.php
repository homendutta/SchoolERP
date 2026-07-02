<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\HostelFeeType;
use App\Modules\Hostel\Models\HostelFee;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Hostel fee definitions. Collection is handled by Finance. */
class HostelFeeService extends BaseCrudService
{
    protected function model(): string
    {
        return HostelFee::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['hostel:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'hostel_id', 'fee_type', 'academic_year_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'amount'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['fee_type' => ['type' => 'enum', 'enum' => HostelFeeType::class]];
    }
}
