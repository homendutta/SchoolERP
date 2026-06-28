<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Models\ClassTimetable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Timetable dashboard — widgets + charts. All counts are derived from the master
 * class timetable.
 */
class TimetableDashboardService
{
    public function __construct(private readonly WorkloadService $workload) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId, int $academicYearId, ?int $templateId = null): array
    {
        $base = fn (): Builder => ClassTimetable::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('academic_year_id', $academicYearId)
            ->when(
                $templateId === null,
                fn ($q) => $q->whereNull('template_id'),
                fn ($q) => $q->where('template_id', $templateId),
            );

        $total = (clone $base())->count();
        $teacherCount = (clone $base())->whereNotNull('teacher_id')->distinct('teacher_id')->count('teacher_id');
        $roomCount = (clone $base())->whereNotNull('room_id')->distinct('room_id')->count('room_id');

        $dailyClasses = (clone $base())->get(['weekday'])
            ->groupBy(fn ($r) => $r->weekday->value)
            ->map(fn ($g, $weekday) => ['period' => $weekday, 'count' => $g->count()])
            ->values()->all();

        $subjectDistribution = (clone $base())->get(['subject_id'])
            ->groupBy('subject_id')
            ->map(fn ($g, $subjectId) => ['subject_id' => (int) $subjectId, 'count' => $g->count()])
            ->values()->all();

        $roomUtilization = (clone $base())->whereNotNull('room_id')->get(['room_id'])
            ->groupBy('room_id')
            ->map(fn ($g, $roomId) => ['room_id' => (int) $roomId, 'count' => $g->count()])
            ->values()->all();

        return [
            'widgets' => [
                'total_timetables' => $total,
                'teacher_load' => $teacherCount,
                'room_usage' => $roomCount,
                'daily_classes' => $total,
            ],
            'charts' => [
                'teacher_workload' => $schoolId !== null ? $this->workload->overview($schoolId, $academicYearId, $templateId) : [],
                'room_utilization' => $roomUtilization,
                'subject_distribution' => $subjectDistribution,
                'daily_classes' => $dailyClasses,
            ],
        ];
    }
}
