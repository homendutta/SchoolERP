<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Resources;

use App\Modules\Timetable\Models\ClassTimetable;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ClassTimetable
 */
class ClassTimetableResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'template_id' => $this->template_id,
            'template' => $this->whenLoaded('template', fn () => $this->template?->name),
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'section_id' => $this->section_id,
            'section' => $this->whenLoaded('section', fn () => $this->section?->name),
            'weekday' => $this->weekday->value,
            'period_id' => $this->period_id,
            'period' => $this->whenLoaded('period', fn () => $this->period?->name),
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn () => $this->subject?->name),
            'teacher_id' => $this->teacher_id,
            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher?->name),
            'room_id' => $this->room_id,
            'room' => $this->whenLoaded('room', fn () => $this->room?->name),
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
