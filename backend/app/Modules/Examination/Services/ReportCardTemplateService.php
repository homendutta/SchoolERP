<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ReportCardTemplate;
use App\Platform\Shared\Services\BaseCrudService;

class ReportCardTemplateService extends BaseCrudService
{
    protected function model(): string
    {
        return ReportCardTemplate::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'is_default'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
