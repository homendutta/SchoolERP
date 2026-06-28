<?php

declare(strict_types=1);

namespace App\Modules\Timetable\DTO;

use App\Modules\Timetable\Enums\Weekday;
use App\Platform\Shared\DTO\DataTransferObject;

/**
 * Normalised input for a single master timetable slot. Built from a validated
 * Form Request and passed into the SaveTimetableEntryAction, which runs clash
 * detection before persisting.
 */
final class TimetableEntryData extends DataTransferObject
{
    public function __construct(
        public readonly int $schoolId,
        public readonly int $academicYearId,
        public readonly int $classId,
        public readonly Weekday $weekday,
        public readonly int $periodId,
        public readonly int $subjectId,
        public readonly ?int $sectionId = null,
        public readonly ?int $teacherId = null,
        public readonly ?int $roomId = null,
        public readonly ?int $templateId = null,
        public readonly string $status = 'active',
    ) {}

    /** @return array<string, mixed> */
    public function toAttributes(): array
    {
        return [
            'school_id' => $this->schoolId,
            'template_id' => $this->templateId,
            'academic_year_id' => $this->academicYearId,
            'class_id' => $this->classId,
            'section_id' => $this->sectionId,
            'weekday' => $this->weekday->value,
            'period_id' => $this->periodId,
            'subject_id' => $this->subjectId,
            'teacher_id' => $this->teacherId,
            'room_id' => $this->roomId,
            'status' => $this->status,
        ];
    }
}
