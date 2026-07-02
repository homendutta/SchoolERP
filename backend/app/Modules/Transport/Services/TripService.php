<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Enums\TripShift;
use App\Modules\Transport\Enums\TripStatus;
use App\Modules\Transport\Models\Trip;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class TripService extends BaseCrudService
{
    protected function model(): string
    {
        return Trip::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'vehicle:id,vehicle_number,seating_capacity,reserved_seats',
            'route:id,name,route_code',
            'driver:id,name,employee_number',
            'attendant:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'vehicle_id', 'route_id', 'driver_id', 'academic_year_id', 'shift', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'departure_time', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'shift' => ['type' => 'enum', 'enum' => TripShift::class],
            'status' => ['type' => 'enum', 'enum' => TripStatus::class],
            'route' => ['type' => 'relation', 'relation' => 'route', 'columns' => ['name', 'route_code']],
        ];
    }
}
