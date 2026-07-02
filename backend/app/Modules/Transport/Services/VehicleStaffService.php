<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Models\VehicleStaff;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Driver/attendant (Staff) assignments to vehicles. */
class VehicleStaffService extends BaseCrudService
{
    protected function model(): string
    {
        return VehicleStaff::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['staff:id,name,employee_number', 'vehicle:id,vehicle_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'vehicle_id', 'staff_id', 'role', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'role'];
    }
}
