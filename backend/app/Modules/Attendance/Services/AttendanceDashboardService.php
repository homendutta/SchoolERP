<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AttendanceDashboardService
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function overview(string $type, ?int $schoolId, array $params = []): array
    {
        $ownerType = $type === 'staff' ? Staff::class : Student::class;
        $from = isset($params['from']) ? Carbon::parse((string) $params['from']) : Carbon::now()->startOfMonth();
        $to = isset($params['to']) ? Carbon::parse((string) $params['to']) : Carbon::now()->endOfMonth();

        $base = fn (): Builder => AttendanceRecord::query()
            ->where('owner_type', $ownerType)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()]);

        $count = fn (AttendanceStatus $s): int => (clone $base())->where('status', $s->value)->count();

        $present = $count(AttendanceStatus::Present);
        $late = $count(AttendanceStatus::Late);
        $halfDay = $count(AttendanceStatus::HalfDay);
        $absent = $count(AttendanceStatus::Absent);
        $leave = $count(AttendanceStatus::Leave);
        $total = (clone $base())->count();

        return [
            'widgets' => [
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'leave' => $leave,
                'attendance_percentage' => $total > 0 ? round((($present + $late + $halfDay) / $total) * 100, 1) : 0.0,
            ],
            'charts' => [
                'daily' => $this->series($base(), 'Y-m-d'),
                'weekly' => $this->series($base(), 'o-\WW'),
                'monthly' => $this->series($base(), 'Y-m'),
            ],
        ];
    }

    /**
     * Present-count series grouped by a date format.
     *
     * @return array<int, array{period:string, count:int}>
     */
    private function series(Builder $query, string $format): array
    {
        return $query->where('status', AttendanceStatus::Present->value)
            ->get(['attendance_date'])
            ->groupBy(fn ($r) => Carbon::parse($r->attendance_date)->format($format))
            ->map(fn ($g, $period) => ['period' => $period, 'count' => $g->count()])
            ->sortKeys()
            ->values()
            ->all();
    }
}
