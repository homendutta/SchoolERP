<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Enums\Weekday;
use App\Modules\Timetable\Models\ClassTimetable;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read + search over the master class timetable. Writes go through the
 * SaveTimetableEntryAction (clash detection) — not the plain create/update.
 */
class ClassTimetableService extends BaseCrudService
{
    protected function model(): string
    {
        return ClassTimetable::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'period:id,name,code,start_time,end_time,sort_order',
            'subject:id,name,code',
            'teacher:id,name,employee_number',
            'room:id,name,code',
            'schoolClass:id,name',
            'section:id,name',
            'template:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return [
            'school_id', 'template_id', 'academic_year_id', 'class_id', 'section_id',
            'weekday', 'period_id', 'subject_id', 'teacher_id', 'room_id', 'status',
        ];
    }

    protected function sortable(): array
    {
        return ['id', 'weekday', 'period_id', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'weekday' => ['type' => 'enum', 'enum' => Weekday::class],
            'teacher' => ['type' => 'relation', 'relation' => 'teacher', 'columns' => ['name', 'employee_number']],
            'subject' => ['type' => 'relation', 'relation' => 'subject', 'columns' => ['name', 'code']],
            'room' => ['type' => 'relation', 'relation' => 'room', 'columns' => ['name', 'code']],
        ];
    }
}
