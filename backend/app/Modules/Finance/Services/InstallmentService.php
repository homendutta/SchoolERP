<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FeeInstallment;
use App\Platform\Shared\Services\BaseCrudService;

class InstallmentService extends BaseCrudService
{
    protected function model(): string
    {
        return FeeInstallment::class;
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_fee_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'due_date'];
    }
}
