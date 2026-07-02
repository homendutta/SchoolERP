<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\BedStatus;
use App\Modules\Hostel\Models\Bed;
use Illuminate\Database\Eloquent\Builder;

/**
 * Occupancy management — derived from bed statuses. Over-allocation is prevented
 * by the allocation engine (a bed can never have two active occupants).
 */
class OccupancyService
{
    /**
     * @return array{total:int, occupied:int, available:int, reserved:int, under_maintenance:int}
     */
    public function summary(?int $schoolId = null, ?int $hostelId = null): array
    {
        $beds = fn (): Builder => Bed::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($hostelId, fn ($q) => $q->whereHas('room', fn ($r) => $r->where('hostel_id', $hostelId)));

        $count = fn (BedStatus $s): int => (clone $beds())->where('status', $s->value)->count();

        return [
            'total' => (clone $beds())->count(),
            'occupied' => $count(BedStatus::Occupied),
            'available' => $count(BedStatus::Available),
            'reserved' => $count(BedStatus::Reserved),
            'under_maintenance' => $count(BedStatus::UnderMaintenance),
        ];
    }
}
