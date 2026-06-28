<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FineRule;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class FineRuleService extends BaseCrudService
{
    protected function model(): string
    {
        return FineRule::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['category:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'fee_category_id', 'mode', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
