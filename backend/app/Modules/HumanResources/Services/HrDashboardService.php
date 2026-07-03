<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\EmploymentStatus;
use App\Modules\HumanResources\Enums\LeaveStatus;
use App\Modules\HumanResources\Models\Department;
use App\Modules\HumanResources\Models\EmploymentRecord;
use App\Modules\HumanResources\Models\LeaveRequest;
use App\Modules\HumanResources\Models\PerformanceReview;
use App\Modules\HumanResources\Models\Separation;
use App\Modules\HumanResources\Models\Training;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class HrDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $scoped = fn (string $model): Builder => $model::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $current = fn (): Builder => EmploymentRecord::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_current', true);

        return [
            'widgets' => [
                'employees' => $scoped(Staff::class)->count(),
                'active' => (clone $current())->where('status', EmploymentStatus::Active->value)->count(),
                'on_leave' => (clone $current())->where('status', EmploymentStatus::OnLeave->value)->count(),
                'departments' => $scoped(Department::class)->count(),
                'pending_leave' => $scoped(LeaveRequest::class)->where('status', LeaveStatus::Pending->value)->count(),
                'trainings' => $scoped(Training::class)->count(),
                'performance_reviews' => $scoped(PerformanceReview::class)->count(),
                'separations' => $scoped(Separation::class)->count(),
            ],
            'charts' => [
                'department_distribution' => (clone $current())->get(['department_id'])
                    ->groupBy('department_id')
                    ->map(fn ($g, $id) => ['label' => $id ? "Dept #{$id}" : 'Unassigned', 'count' => $g->count()])
                    ->values()->all(),
                'leave_trend' => $this->trend($scoped(LeaveRequest::class), 'applied_on'),
                'attendance_trend' => $this->trend($scoped(EmploymentRecord::class), 'created_at'),
                'performance_distribution' => $scoped(PerformanceReview::class)->get(['status'])
                    ->groupBy(fn ($r) => $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status)
                    ->map(fn ($g, $s) => ['label' => (string) $s, 'count' => $g->count()])
                    ->values()->all(),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function trend(Builder $query, string $column): array
    {
        return $query->whereNotNull($column)->get([$column])
            ->groupBy(fn ($m) => Carbon::parse($m->getAttribute($column))->format('Y-m-d'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => $g->count()])
            ->sortKeys()->values()->take(-14)->all();
    }
}
