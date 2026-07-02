<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\WarrantyStatus;
use App\Modules\Inventory\Models\Warranty;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class WarrantyService extends BaseCrudService
{
    protected function model(): string
    {
        return Warranty::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['asset:id,asset_number', 'vendor:id,name']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'asset_id', 'vendor_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'end_date'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['status' => ['type' => 'enum', 'enum' => WarrantyStatus::class], 'end_date' => ['type' => 'date']];
    }
}
