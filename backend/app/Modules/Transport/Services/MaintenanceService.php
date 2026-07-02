<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Models\Maintenance;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Vehicle maintenance schedules (planning only). */
class MaintenanceService extends BaseCrudService
{
    protected function model(): string
    {
        return Maintenance::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['vehicle:id,vehicle_number']);
    }

    protected function searchable(): array
    {
        return ['service_type'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'vehicle_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'service_due_date'];
    }
}
