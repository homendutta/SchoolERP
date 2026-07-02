<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Asset;
use App\Platform\Foundation\Maintenance\Enums\MaintenancePriority;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceStatus;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceType;
use App\Platform\Foundation\Maintenance\Models\MaintenanceRequest;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read + search over asset maintenance. Writes go through the reusable Platform
 * Maintenance Engine; here we scope the shared maintenance_requests table to
 * asset maintainables.
 */
class MaintenanceService extends BaseCrudService
{
    protected function model(): string
    {
        return MaintenanceRequest::class;
    }

    protected function query(): Builder
    {
        return MaintenanceRequest::query()->where('maintainable_type', Asset::class);
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['maintainable:id,asset_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'type', 'priority', 'status', 'assigned_staff_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'scheduled_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'type' => ['type' => 'enum', 'enum' => MaintenanceType::class],
            'priority' => ['type' => 'enum', 'enum' => MaintenancePriority::class],
            'status' => ['type' => 'enum', 'enum' => MaintenanceStatus::class],
        ];
    }
}
