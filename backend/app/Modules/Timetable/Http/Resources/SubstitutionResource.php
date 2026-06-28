<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Resources;

use App\Modules\Timetable\Models\TimetableSubstitution;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin TimetableSubstitution
 */
class SubstitutionResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'class_timetable_id' => $this->class_timetable_id,
            'academic_year_id' => $this->academic_year_id,
            'original_teacher_id' => $this->original_teacher_id,
            'original_teacher' => $this->whenLoaded('originalTeacher', fn () => $this->originalTeacher?->name),
            'substitute_teacher_id' => $this->substitute_teacher_id,
            'substitute_teacher' => $this->whenLoaded('substituteTeacher', fn () => $this->substituteTeacher?->name),
            'date' => $this->date?->toDateString(),
            'period_id' => $this->period_id,
            'period' => $this->whenLoaded('period', fn () => $this->period?->name),
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn () => $this->subject?->name),
            'reason' => $this->reason,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
