<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\CalendarEvent;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin CalendarEvent
 */
class CalendarEventResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_calendar_id' => $this->academic_calendar_id,
            'holiday_type_id' => $this->holiday_type_id,
            'holiday_type' => $this->whenLoaded('holidayType', fn () => $this->holidayType?->only(['id', 'name', 'color'])),
            'title' => $this->title,
            'description' => $this->description,
            'event_type' => $this->event_type?->value,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_recurring' => (bool) $this->is_recurring,
            'status' => $this->status?->value,
            'archived' => $this->trashed(),
        ];
    }
}
