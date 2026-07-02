<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\FineRule;
use App\Platform\Shared\Services\BaseCrudService;

class FineRuleService extends BaseCrudService
{
    protected function model(): string
    {
        return FineRule::class;
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'mode', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
