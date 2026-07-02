<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\MovementType;
use App\Modules\Inventory\Models\StockMovement;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read over the append-only stock movement history. */
class MovementService extends BaseCrudService
{
    protected function model(): string
    {
        return StockMovement::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['consumable:id,name,unit']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'consumable_id', 'type'];
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
        return ['type' => ['type' => 'enum', 'enum' => MovementType::class]];
    }
}
