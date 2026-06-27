<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\Term;
use App\Platform\Shared\Services\BaseCrudService;

class TermService extends BaseCrudService
{
    protected function model(): string
    {
        return Term::class;
    }

    protected function searchable(): array
    {
        return ['name', 'short_name'];
    }

    protected function filterable(): array
    {
        return ['academic_year_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'sort_order', 'start_date', 'created_at'];
    }
}
