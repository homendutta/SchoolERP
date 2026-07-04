<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Enums\ExportStatus;
use App\Modules\Reports\Models\ReportExport;
use App\Platform\Shared\Services\BaseCrudService;

/** Read over export history / queue. */
class ExportHistoryService extends BaseCrudService
{
    protected function model(): string
    {
        return ReportExport::class;
    }

    protected function searchable(): array
    {
        return ['report_name', 'report_key'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'report_key', 'format', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['status' => ['type' => 'enum', 'enum' => ExportStatus::class]];
    }
}
