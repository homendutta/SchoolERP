<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\AcademicCalendar;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class AcademicCalendarService extends BaseCrudService
{
    protected function model(): string
    {
        return AcademicCalendar::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('academicYear:id,name')->withCount('events');
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['academic_year_id', 'status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
