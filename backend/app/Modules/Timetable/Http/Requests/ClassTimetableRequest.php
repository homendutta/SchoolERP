<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Requests;

use App\Modules\Timetable\DTO\TimetableEntryData;
use App\Modules\Timetable\Enums\Weekday;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ClassTimetableRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // A slot save always carries the full slot (create and update) so clash
        // detection has the complete context.
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'weekday' => ['required', Rule::in(Weekday::values())],
            'period_id' => ['required', 'integer', 'exists:timetable_periods,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:staff,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'template_id' => ['nullable', 'integer', 'exists:timetable_templates,id'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }

    public function toData(): TimetableEntryData
    {
        /** @var array<string, mixed> $v */
        $v = $this->validated();

        return new TimetableEntryData(
            schoolId: (int) $v['school_id'],
            academicYearId: (int) $v['academic_year_id'],
            classId: (int) $v['class_id'],
            weekday: Weekday::from((string) $v['weekday']),
            periodId: (int) $v['period_id'],
            subjectId: (int) $v['subject_id'],
            sectionId: isset($v['section_id']) ? (int) $v['section_id'] : null,
            teacherId: isset($v['teacher_id']) ? (int) $v['teacher_id'] : null,
            roomId: isset($v['room_id']) ? (int) $v['room_id'] : null,
            templateId: isset($v['template_id']) ? (int) $v['template_id'] : null,
            status: (string) ($v['status'] ?? 'active'),
        );
    }
}
