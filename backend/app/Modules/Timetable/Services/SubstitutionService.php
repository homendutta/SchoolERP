<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Enums\SubstitutionStatus;
use App\Modules\Timetable\Models\TimetableSubstitution;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class SubstitutionService extends BaseCrudService
{
    protected function model(): string
    {
        return TimetableSubstitution::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'originalTeacher:id,name,employee_number',
            'substituteTeacher:id,name,employee_number',
            'period:id,name,code',
            'subject:id,name,code',
            'schoolClass:id,name',
            'section:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'date', 'period_id', 'original_teacher_id', 'substitute_teacher_id', 'class_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'date' => ['type' => 'date'],
            'status' => ['type' => 'enum', 'enum' => SubstitutionStatus::class],
            'substitute' => ['type' => 'relation', 'relation' => 'substituteTeacher', 'columns' => ['name', 'employee_number']],
        ];
    }
}
