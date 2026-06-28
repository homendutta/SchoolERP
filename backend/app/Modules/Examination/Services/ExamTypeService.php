<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamType;
use App\Platform\Shared\Services\BaseCrudService;

class ExamTypeService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamType::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'is_active', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'name', 'created_at'];
    }
}
