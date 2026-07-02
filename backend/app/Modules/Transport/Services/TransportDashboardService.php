<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Enums\AssignmentStatus;
use App\Modules\Transport\Models\Maintenance;
use App\Modules\Transport\Models\StudentAssignment;
use App\Modules\Transport\Models\TransportRoute;
use App\Modules\Transport\Models\Trip;
use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Models\VehicleStaff;
use Illuminate\Database\Eloquent\Builder;

class TransportDashboardService
{
    public function __construct(private readonly CapacityService $capacity) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $scope = fn (Builder $q): Builder => $q->when($schoolId, fn ($x) => $x->where('school_id', $schoolId));

        $routes = TransportRoute::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get(['id', 'name']);

        $routeUsage = [];
        $capacityUsage = [];
        $overCapacity = 0;
        foreach ($routes as $route) {
            $assigned = $this->capacity->routeAssignedCount((int) $route->id);
            $cap = $this->capacity->routeCapacity((int) $route->id);
            $routeUsage[] = ['label' => $route->name, 'count' => $assigned];
            $capacityUsage[] = ['label' => $route->name, 'assigned' => $assigned, 'capacity' => $cap];
            if ($cap > 0 && $assigned > $cap) {
                $overCapacity++;
            }
        }

        $maintenanceDue = Maintenance::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereDate('service_due_date', '<=', now()->addDays(7)->toDateString())
            ->where('status', 'scheduled')->count();

        return [
            'widgets' => [
                'vehicles' => $scope(Vehicle::query())->count(),
                'routes' => $routes->count(),
                'trips' => $scope(Trip::query())->count(),
                'assigned_students' => $scope(StudentAssignment::query())->where('status', AssignmentStatus::Active->value)->count(),
                'drivers' => $scope(VehicleStaff::query())->whereIn('role', ['primary_driver', 'backup_driver'])->distinct('staff_id')->count('staff_id'),
                'over_capacity' => $overCapacity,
                'maintenance_due' => $maintenanceDue,
            ],
            'charts' => [
                'route_usage' => $routeUsage,
                'vehicle_utilization' => $this->vehicleUtilization($schoolId),
                'student_distribution' => $routeUsage,
                'capacity_usage' => $capacityUsage,
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function vehicleUtilization(?int $schoolId): array
    {
        return Trip::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get(['vehicle_id'])
            ->groupBy('vehicle_id')
            ->map(fn ($g, $vehicleId) => ['label' => "Vehicle #{$vehicleId}", 'count' => $g->count()])
            ->values()->all();
    }
}
