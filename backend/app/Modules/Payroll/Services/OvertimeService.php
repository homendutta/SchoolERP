<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\Overtime;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Overtime entries. Payroll only calculates APPROVED overtime. */
class OvertimeService extends BaseCrudService
{
    protected function model(): string
    {
        return Overtime::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'period_year', 'period_month', 'approved', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'period_year', 'period_month'];
    }
}
