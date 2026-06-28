<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamGrade;
use App\Platform\Shared\Services\BaseCrudService;

class ExamGradeService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamGrade::class;
    }

    protected function searchable(): array
    {
        return ['code', 'name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'is_failing'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'min_percentage'];
    }
}
