<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\AcademicCalendar;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AcademicCalendar
 */
class AcademicCalendarResource extends BaseResource
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
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear?->only(['id', 'name'])),
            'name' => $this->name,
            'status' => $this->status?->value,
            'events' => CalendarEventResource::collection($this->whenLoaded('events')),
            'archived' => $this->trashed(),
        ];
    }
}
