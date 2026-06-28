<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Enums\Weekday;
use App\Modules\Timetable\Models\ClassTimetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Teacher and Room timetables, DERIVED on demand from the master class
 * timetable. Nothing is stored separately — there is one source of truth.
 */
class DerivedTimetableService
{
    /** @return Collection<int, ClassTimetable> */
    public function forTeacher(int $teacherId, int $academicYearId, ?int $templateId = null): Collection
    {
        return $this->ordered($this->base($academicYearId, $templateId)->where('teacher_id', $teacherId));
    }

    /** @return Collection<int, ClassTimetable> */
    public function forRoom(int $roomId, int $academicYearId, ?int $templateId = null): Collection
    {
        return $this->ordered($this->base($academicYearId, $templateId)->where('room_id', $roomId));
    }

    /** @return Collection<int, ClassTimetable> */
    public function forClass(int $classId, ?int $sectionId, int $academicYearId, ?int $templateId = null): Collection
    {
        $query = $this->base($academicYearId, $templateId)
            ->where('class_id', $classId)
            ->when(
                $sectionId === null,
                fn ($q) => $q->whereNull('section_id'),
                fn ($q) => $q->where('section_id', $sectionId),
            );

        return $this->ordered($query);
    }

    private function base(int $academicYearId, ?int $templateId): Builder
    {
        return ClassTimetable::query()
            ->where('academic_year_id', $academicYearId)
            ->when(
                $templateId === null,
                fn ($q) => $q->whereNull('template_id'),
                fn ($q) => $q->where('template_id', $templateId),
            )
            ->with([
                'period:id,name,code,start_time,end_time,sort_order',
                'subject:id,name,code',
                'teacher:id,name,employee_number',
                'room:id,name,code',
                'schoolClass:id,name',
                'section:id,name',
            ]);
    }

    /**
     * @return Collection<int, ClassTimetable>
     */
    private function ordered(Builder $query): Collection
    {
        /** @var Collection<int, ClassTimetable> $rows */
        $rows = $query->get();

        return $rows->sortBy([
            fn (ClassTimetable $a, ClassTimetable $b) => ($a->weekday instanceof Weekday ? $a->weekday->sortOrder() : 0)
                <=> ($b->weekday instanceof Weekday ? $b->weekday->sortOrder() : 0),
            fn (ClassTimetable $a, ClassTimetable $b) => ($a->period?->sort_order ?? 0) <=> ($b->period?->sort_order ?? 0),
        ])->values();
    }
}
