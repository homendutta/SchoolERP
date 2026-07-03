<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\LeavePolicy;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Leave policies (allocation / carry-forward / encashment / approval levels). */
class LeavePolicyService extends BaseCrudService
{
    protected function model(): string
    {
        return LeavePolicy::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['leaveType:id,name']);
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'leave_type_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
