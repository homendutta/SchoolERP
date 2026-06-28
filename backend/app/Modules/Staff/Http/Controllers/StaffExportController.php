<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffService;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Export\CsvExporter;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Staff Export — reuses the Export engine and the same filters as search. CSV is
 * implemented; XLSX/PDF are pluggable and reported unavailable for now.
 */
class StaffExportController extends BaseController
{
    public function export(Request $request, StaffService $service): StreamedResponse
    {
        $format = (string) $request->input('format', 'csv');

        $page = $service->list(array_merge($request->all(), ['per_page' => 1000]));

        $headings = ['Employee No', 'Name', 'Department', 'Designation', 'Type', 'Status', 'Joining Date'];
        $rows = array_map(static function (Staff $s): array {
            return [
                $s->employee_number,
                $s->name,
                $s->department?->label,
                $s->designation?->label,
                $s->employment_type?->value,
                $s->status?->value,
                $s->joining_date?->toDateString(),
            ];
        }, $page->items());

        $exporter = match ($format) {
            'csv' => new CsvExporter,
            default => throw BusinessRuleException::make(
                "Export format '{$format}' is not available yet.",
                'EXPORT_FORMAT_UNAVAILABLE',
            ),
        };

        return $exporter->download('staff', $headings, $rows);
    }
}
