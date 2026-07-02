<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Enums\AssignmentStatus;
use App\Modules\Transport\Enums\TripStatus;
use App\Modules\Transport\Models\Stop;
use App\Modules\Transport\Models\StudentAssignment;
use App\Modules\Transport\Models\Trip;
use App\Modules\Transport\Models\Vehicle;
use App\Platform\Shared\Exceptions\BusinessRuleException;

/**
 * Capacity enforcement — never over-allocate. Route/vehicle capacity is derived
 * from the vehicles running scheduled trips on the route (a student's vehicle is
 * determined through the trip, never assigned directly). Reserved seats are
 * honoured; per-stop capacity is enforced when set.
 */
class CapacityService
{
    /** Total student seats available on a route = sum of trip vehicles' free seats. */
    public function routeCapacity(int $routeId): int
    {
        $vehicleIds = Trip::query()
            ->where('route_id', $routeId)
            ->whereIn('status', [TripStatus::Scheduled->value, TripStatus::Running->value])
            ->pluck('vehicle_id')->unique();

        return (int) Vehicle::query()->whereIn('id', $vehicleIds)->get()
            ->sum(fn (Vehicle $v) => $v->availableSeats());
    }

    public function routeAssignedCount(int $routeId): int
    {
        return StudentAssignment::query()
            ->where('route_id', $routeId)
            ->where('status', AssignmentStatus::Active->value)
            ->count();
    }

    public function stopAssignedCount(int $stopId): int
    {
        return StudentAssignment::query()
            ->where('stop_id', $stopId)
            ->where('status', AssignmentStatus::Active->value)
            ->count();
    }

    /** Assert a new assignment fits vehicle/route and stop capacity. */
    public function assertCanAssign(int $routeId, Stop $stop, ?int $ignoreStudentId = null): void
    {
        $capacity = $this->routeCapacity($routeId);
        $assigned = $this->routeAssignedCount($routeId);
        if ($ignoreStudentId !== null) {
            $assigned -= StudentAssignment::query()
                ->where('route_id', $routeId)->where('student_id', $ignoreStudentId)
                ->where('status', AssignmentStatus::Active->value)->count();
        }

        if ($capacity > 0 && $assigned >= $capacity) {
            throw BusinessRuleException::make('Route/vehicle capacity is full.', 'OVER_CAPACITY');
        }

        if ($stop->capacity !== null && $stop->capacity > 0 && $this->stopAssignedCount((int) $stop->id) >= $stop->capacity) {
            throw BusinessRuleException::make('This stop is at capacity.', 'STOP_FULL');
        }
    }
}
