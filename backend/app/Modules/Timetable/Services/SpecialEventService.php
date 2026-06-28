<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Enums\SpecialEventType;
use App\Modules\Timetable\Models\TimetableSpecialEvent;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class SpecialEventService extends BaseCrudService
{
    protected function model(): string
    {
        return TimetableSpecialEvent::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['schoolClass:id,name', 'section:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'academic_year_id', 'event_type', 'scope', 'class_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'start_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'start_date' => ['type' => 'date'],
            'event_type' => ['type' => 'enum', 'enum' => SpecialEventType::class],
        ];
    }
}
