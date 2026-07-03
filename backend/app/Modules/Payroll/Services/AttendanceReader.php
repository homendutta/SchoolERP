<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Staff\Models\Staff;
use Illuminate\Support\Carbon;

/**
 * READ-ONLY view of the Attendance module for a staff member and payroll period.
 * Payroll consumes attendance but NEVER modifies it — the Attendance module
 * remains the source of truth. Records are identity/owner based (owner = Staff).
 *
 * @phpstan-type AttendanceSummary array{present:float, absent:float, half_day:float, leave:float, working:float}
 */
class AttendanceReader
{
    /**
     * Summarise a staff member's attendance for a month.
     *
     * @return array{present:float, absent:float, half_day:float, leave:float, working:float}
     */
    public function summarise(int $staffId, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $records = AttendanceRecord::query()
            ->where('owner_type', Staff::class)
            ->where('owner_id', $staffId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get(['status']);

        $count = fn (AttendanceStatus $status): float => (float) $records
            ->filter(fn ($r) => $r->status === $status)->count();

        $present = $count(AttendanceStatus::Present) + $count(AttendanceStatus::Late);
        $absent = $count(AttendanceStatus::Absent);
        $halfDay = $count(AttendanceStatus::HalfDay);
        $leave = $count(AttendanceStatus::Leave);

        // Working (scheduled) days exclude weekend/holiday markers.
        $working = $present + $absent + $halfDay + $leave;

        return [
            'present' => $present,
            'absent' => $absent,
            'half_day' => $halfDay,
            'leave' => $leave,
            'working' => $working,
        ];
    }
}
