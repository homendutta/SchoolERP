<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\AllocationStatus;
use App\Modules\Hostel\Models\Allocation;
use App\Modules\Hostel\Models\Building;
use App\Modules\Hostel\Models\Hostel;
use App\Modules\Hostel\Models\Maintenance;
use App\Modules\Hostel\Models\Room;
use App\Modules\Hostel\Models\Visitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class HostelDashboardService
{
    public function __construct(private readonly OccupancyService $occupancy) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $scope = fn (Builder $q): Builder => $q->when($schoolId, fn ($x) => $x->where('school_id', $schoolId));
        $occ = $this->occupancy->summary($schoolId);

        return [
            'widgets' => [
                'hostels' => $scope(Hostel::query())->count(),
                'buildings' => $scope(Building::query())->count(),
                'rooms' => $scope(Room::query())->count(),
                'beds' => $occ['total'],
                'occupied' => $occ['occupied'],
                'available' => $occ['available'],
                'visitors_today' => $scope(Visitor::query())->whereDate('visit_date', Carbon::now()->toDateString())->count(),
                'pending_maintenance' => $scope(Maintenance::query())->whereIn('status', ['open', 'in_progress'])->count(),
            ],
            'charts' => [
                'occupancy' => [
                    ['label' => 'Occupied', 'count' => $occ['occupied']],
                    ['label' => 'Available', 'count' => $occ['available']],
                    ['label' => 'Reserved', 'count' => $occ['reserved']],
                    ['label' => 'Maintenance', 'count' => $occ['under_maintenance']],
                ],
                'hostel_distribution' => $this->byHostel($schoolId),
                'maintenance_trend' => $this->maintenanceTrend($schoolId),
                'student_allocation' => $this->allocationTrend($schoolId),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function byHostel(?int $schoolId): array
    {
        return Allocation::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', AllocationStatus::Active->value)
            ->get(['hostel_id'])
            ->groupBy('hostel_id')
            ->map(fn ($g, $hostelId) => ['label' => "Hostel #{$hostelId}", 'count' => $g->count()])
            ->values()->all();
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function maintenanceTrend(?int $schoolId): array
    {
        return Maintenance::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get(['created_at'])
            ->groupBy(fn ($m) => Carbon::parse($m->created_at)->format('Y-m-d'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => $g->count()])
            ->sortKeys()->values()->take(-14)->all();
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function allocationTrend(?int $schoolId): array
    {
        return Allocation::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get(['allocation_date'])
            ->groupBy(fn ($a) => Carbon::parse($a->allocation_date)->format('Y-m'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => $g->count()])
            ->sortKeys()->values()->take(-12)->all();
    }
}
