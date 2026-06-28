<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Modules\Staff\Models\StaffQualification;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class StaffQualificationService extends BaseCrudService
{
    protected function model(): string
    {
        return StaffQualification::class;
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
        return ['id', 'year', 'created_at'];
    }
}
