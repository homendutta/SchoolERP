<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Models\SavedReport;
use App\Platform\Shared\Services\BaseCrudService;

/** User-saved reports (reusable filters/columns/sorting). */
class SavedReportService extends BaseCrudService
{
    protected function model(): string
    {
        return SavedReport::class;
    }

    protected function searchable(): array
    {
        return ['name', 'report_key'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'user_id', 'report_key'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
