<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\DTO\TimetableEntryData;
use App\Modules\Timetable\Models\ClassTimetable;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prevents invalid schedules. Clashes are evaluated within the same academic
 * year and template scope (different templates — e.g. Summer vs Winter — never
 * clash because they are not active simultaneously).
 *
 *   - TEACHER_CLASH : one teacher in two slots at the same weekday + period
 *   - ROOM_CLASH    : one room hosting two slots at the same weekday + period
 *   - CLASS_CLASH   : one class+section with two subjects in the same period
 */
class ClashDetector
{
    /**
     * @return array<int, array{type:string, message:string}> the clashes found
     */
    public function detect(TimetableEntryData $data, ?int $ignoreId = null): array
    {
        $clashes = [];

        $scope = fn (): Builder => ClassTimetable::query()
            ->where('academic_year_id', $data->academicYearId)
            ->where('weekday', $data->weekday->value)
            ->where('period_id', $data->periodId)
            ->when(
                $data->templateId === null,
                fn ($q) => $q->whereNull('template_id'),
                fn ($q) => $q->where('template_id', $data->templateId),
            )
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId));

        $classClash = (clone $scope())
            ->where('class_id', $data->classId)
            ->when(
                $data->sectionId === null,
                fn ($q) => $q->whereNull('section_id'),
                fn ($q) => $q->where('section_id', $data->sectionId),
            )
            ->exists();
        if ($classClash) {
            $clashes[] = ['type' => 'CLASS_CLASH', 'message' => 'This class already has a subject in this period.'];
        }

        if ($data->teacherId !== null) {
            $teacherClash = (clone $scope())->where('teacher_id', $data->teacherId)->exists();
            if ($teacherClash) {
                $clashes[] = ['type' => 'TEACHER_CLASH', 'message' => 'This teacher is already assigned to another class in this period.'];
            }
        }

        if ($data->roomId !== null) {
            $roomClash = (clone $scope())->where('room_id', $data->roomId)->exists();
            if ($roomClash) {
                $clashes[] = ['type' => 'ROOM_CLASH', 'message' => 'This room is already booked for another class in this period.'];
            }
        }

        return $clashes;
    }

    /**
     * Assert the slot is valid; throws on the first clash so an invalid schedule
     * can never be saved.
     */
    public function assertNoClash(TimetableEntryData $data, ?int $ignoreId = null): void
    {
        $clashes = $this->detect($data, $ignoreId);

        if ($clashes !== []) {
            throw BusinessRuleException::make($clashes[0]['message'], $clashes[0]['type']);
        }
    }
}
