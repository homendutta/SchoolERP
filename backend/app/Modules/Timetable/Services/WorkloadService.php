<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Models\ClassTimetable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Teacher workload, calculated from the master class timetable (never in the
 * UI). Exposes periods-per-day, periods-per-week, subject load and class load.
 */
class WorkloadService
{
    /**
     * Workload for one teacher within an academic year (optionally a template).
     *
     * @return array{
     *     teacher_id:int,
     *     periods_per_week:int,
     *     periods_per_day:array<int, array{weekday:string, count:int}>,
     *     subject_load:array<int, array{subject_id:int, count:int}>,
     *     class_load:array<int, array{class_id:int, section_id:int|null, count:int}>
     * }
     */
    public function forTeacher(int $teacherId, int $academicYearId, ?int $templateId = null): array
    {
        $rows = $this->base($academicYearId, $templateId)
            ->where('teacher_id', $teacherId)
            ->get(['weekday', 'subject_id', 'class_id', 'section_id']);

        $perDay = $rows->groupBy(fn ($r) => $r->weekday->value)
            ->map(fn ($g, $weekday) => ['weekday' => $weekday, 'count' => $g->count()])
            ->values()->all();

        $subjectLoad = $rows->groupBy('subject_id')
            ->map(fn ($g, $subjectId) => ['subject_id' => (int) $subjectId, 'count' => $g->count()])
            ->values()->all();

        $classLoad = $rows->groupBy(fn ($r) => $r->class_id.'-'.($r->section_id ?? '0'))
            ->map(fn ($g) => [
                'class_id' => (int) $g->first()->class_id,
                'section_id' => $g->first()->section_id !== null ? (int) $g->first()->section_id : null,
                'count' => $g->count(),
            ])
            ->values()->all();

        return [
            'teacher_id' => $teacherId,
            'periods_per_week' => $rows->count(),
            'periods_per_day' => $perDay,
            'subject_load' => $subjectLoad,
            'class_load' => $classLoad,
        ];
    }

    /**
     * Per-teacher period totals across the school — drives the workload chart.
     *
     * @return array<int, array{teacher_id:int, periods_per_week:int}>
     */
    public function overview(int $schoolId, int $academicYearId, ?int $templateId = null): array
    {
        return $this->base($academicYearId, $templateId)
            ->where('school_id', $schoolId)
            ->whereNotNull('teacher_id')
            ->get(['teacher_id'])
            ->groupBy('teacher_id')
            ->map(fn ($g, $teacherId) => ['teacher_id' => (int) $teacherId, 'periods_per_week' => $g->count()])
            ->values()->all();
    }

    private function base(int $academicYearId, ?int $templateId): Builder
    {
        return ClassTimetable::query()
            ->where('academic_year_id', $academicYearId)
            ->when(
                $templateId === null,
                fn ($q) => $q->whereNull('template_id'),
                fn ($q) => $q->where('template_id', $templateId),
            );
    }
}
