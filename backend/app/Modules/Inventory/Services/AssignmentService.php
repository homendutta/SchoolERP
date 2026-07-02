<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AssignmentStatus;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over asset assignments (writes go through the engine). */
class AssignmentService extends BaseCrudService
{
    protected function model(): string
    {
        return AssetAssignment::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['asset:id,asset_number,serial_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'asset_id', 'target_type', 'identity_id', 'owner_type', 'owner_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'assigned_on', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => AssignmentStatus::class],
            'asset' => ['type' => 'relation', 'relation' => 'asset', 'columns' => ['asset_number', 'serial_number']],
        ];
    }
}
