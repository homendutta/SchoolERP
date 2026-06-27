<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\Section;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class SectionService extends BaseCrudService
{
    protected function model(): string
    {
        return Section::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['schoolClass:id,name,code', 'room:id,name,code']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['class_id', 'room_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'display_order', 'created_at'];
    }
}
