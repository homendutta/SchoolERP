<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Enums\CalendarEventType;
use App\Modules\Academic\Models\CalendarEvent;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class CalendarEventService extends BaseCrudService
{
    protected function model(): string
    {
        return CalendarEvent::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('holidayType:id,name,color');
    }

    protected function searchable(): array
    {
        return ['title', 'description'];
    }

    protected function filterable(): array
    {
        return ['academic_calendar_id', 'event_type', 'holiday_type_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'title', 'start_date', 'created_at'];
    }

    protected function searchDefinitions(): array
    {
        return [
            'event_type' => ['type' => 'enum', 'enum' => CalendarEventType::class],
            'start_date' => ['type' => 'date'],
        ];
    }
}
