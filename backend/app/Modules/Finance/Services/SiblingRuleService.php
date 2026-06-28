<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\SiblingDiscountRule;
use App\Platform\Shared\Services\BaseCrudService;

class SiblingRuleService extends BaseCrudService
{
    protected function model(): string
    {
        return SiblingDiscountRule::class;
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'child_order', 'method', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'child_order'];
    }
}
