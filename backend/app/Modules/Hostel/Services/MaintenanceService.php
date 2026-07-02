<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\MaintenanceCategory;
use App\Modules\Hostel\Enums\MaintenancePriority;
use App\Modules\Hostel\Enums\MaintenanceStatus;
use App\Modules\Hostel\Models\Maintenance;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Hostel maintenance requests (no workflow engine). */
class MaintenanceService extends BaseCrudService
{
    protected function model(): string
    {
        return Maintenance::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['hostel:id,name', 'assignedStaff:id,name']);
    }

    protected function searchable(): array
    {
        return ['description'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'hostel_id', 'room_id', 'category', 'priority', 'status', 'assigned_staff_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'priority', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'category' => ['type' => 'enum', 'enum' => MaintenanceCategory::class],
            'priority' => ['type' => 'enum', 'enum' => MaintenancePriority::class],
            'status' => ['type' => 'enum', 'enum' => MaintenanceStatus::class],
        ];
    }
}
