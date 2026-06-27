<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\SchoolClass;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassService extends BaseCrudService
{
    protected function model(): string
    {
        return SchoolClass::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->withCount('sections');
    }

    protected function searchable(): array
    {
        return ['code', 'name', 'short_name', 'slug'];
    }

    protected function filterable(): array
    {
        return ['status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'code', 'name', 'display_order', 'created_at'];
    }
}
