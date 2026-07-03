<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\LeaveBalance;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read-only listing of employee leave balances (maintained by the Leave Engine). */
class LeaveBalanceService extends BaseCrudService
{
    protected function model(): string
    {
        return LeaveBalance::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number', 'leaveType:id,name']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'leave_type_id', 'year'];
    }

    protected function sortable(): array
    {
        return ['id', 'year'];
    }
}
