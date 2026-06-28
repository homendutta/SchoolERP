<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Models\TimetableTemplate;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class TemplateService extends BaseCrudService
{
    protected function model(): string
    {
        return TimetableTemplate::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['academicYear:id,name'])->withCount('entries');
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'academic_year_id', 'is_active', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
