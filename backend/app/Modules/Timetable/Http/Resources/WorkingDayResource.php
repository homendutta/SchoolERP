<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Resources;

use App\Modules\Timetable\Models\TimetableWorkingDay;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin TimetableWorkingDay
 */
class WorkingDayResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'weekday' => $this->weekday->value,
            'is_working' => $this->is_working,
            'sort_order' => $this->sort_order,
        ];
    }
}
