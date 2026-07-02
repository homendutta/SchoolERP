<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Enums\TransportFeeType;
use App\Modules\Transport\Models\TransportFee;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Transport fee definitions. Collection is handled by Finance. */
class TransportFeeService extends BaseCrudService
{
    protected function model(): string
    {
        return TransportFee::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['route:id,name', 'stop:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'fee_type', 'route_id', 'stop_id', 'academic_year_id', 'status'];
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
        return ['fee_type' => ['type' => 'enum', 'enum' => TransportFeeType::class]];
    }
}
