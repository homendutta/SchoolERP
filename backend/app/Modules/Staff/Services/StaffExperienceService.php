<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Modules\Staff\Models\StaffExperience;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class StaffExperienceService extends BaseCrudService
{
    protected function model(): string
    {
        return StaffExperience::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('certificate:id,uuid');
    }

    protected function filterable(): array
    {
        return ['staff_id', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'from_date', 'created_at'];
    }
}
