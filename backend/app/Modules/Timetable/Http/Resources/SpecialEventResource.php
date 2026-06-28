<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Resources;

use App\Modules\Timetable\Models\TimetableSpecialEvent;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin TimetableSpecialEvent
 */
class SpecialEventResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'event_type' => $this->event_type->value,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'scope' => $this->scope,
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'section_id' => $this->section_id,
            'cancels_classes' => $this->cancels_classes,
            'description' => $this->description,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
