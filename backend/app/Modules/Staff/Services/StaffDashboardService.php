<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Modules\Staff\Enums\StaffStatus;
use App\Modules\Staff\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StaffDashboardService
{
    /** @return array<string, mixed> */
    public function overview(?int $schoolId = null): array
    {
        $staff = fn () => Staff::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        return [
            'widgets' => [
                'total_staff' => (clone $staff())->count(),
                'teaching_staff' => (clone $staff())->where('is_teaching', true)->count(),
                'non_teaching_staff' => (clone $staff())->where('is_teaching', false)->count(),
                'active' => (clone $staff())->where('status', StaffStatus::Active->value)->count(),
                'on_leave' => (clone $staff())->where('status', StaffStatus::OnLeave->value)->count(),
                'new_joinees' => (clone $staff())
                    ->whereBetween('joining_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->count(),
                'resigned' => (clone $staff())->where('status', StaffStatus::Resigned->value)->count(),
            ],
            'charts' => [
                'department_distribution' => $this->distribution($schoolId, 'department_id', 'department'),
                'designation_distribution' => $this->distribution($schoolId, 'designation_id', 'designation'),
                'monthly_joining' => $this->monthly((clone $staff())->whereNotNull('joining_date')->pluck('joining_date')),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function distribution(?int $schoolId, string $column, string $relation): array
    {
        return Staff::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with("{$relation}:id,label")
            ->select($column, DB::raw('count(*) as aggregate'))
            ->groupBy($column)
            ->get()
            ->map(fn ($r) => ['label' => $r->{$relation}?->label ?? 'Unassigned', 'count' => (int) $r->aggregate])
            ->all();
    }

    /**
     * @param  iterable<int, mixed>  $dates
     * @return array<int, array{month:string, count:int}>
     */
    private function monthly(iterable $dates): array
    {
        $buckets = [];
        foreach ($dates as $date) {
            if ($date === null) {
                continue;
            }
            $month = Carbon::parse($date)->format('Y-m');
            $buckets[$month] = ($buckets[$month] ?? 0) + 1;
        }
        ksort($buckets);

        return array_map(static fn ($month, $count) => ['month' => $month, 'count' => $count], array_keys($buckets), $buckets);
    }
}
