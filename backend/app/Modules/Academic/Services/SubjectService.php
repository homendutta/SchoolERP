<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\Subject;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class SubjectService extends BaseCrudService
{
    protected function model(): string
    {
        return Subject::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('subjectType:id,label,value');
    }

    protected function searchable(): array
    {
        return ['code', 'name', 'short_name', 'slug'];
    }

    protected function filterable(): array
    {
        return ['subject_type_id', 'status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'code', 'name', 'display_order', 'created_at'];
    }
}
