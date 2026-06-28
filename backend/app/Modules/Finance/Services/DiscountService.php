<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Discount;
use App\Platform\Shared\Services\BaseCrudService;

class DiscountService extends BaseCrudService
{
    protected function model(): string
    {
        return Discount::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'method', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
