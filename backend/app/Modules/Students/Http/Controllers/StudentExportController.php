<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentService;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Export\CsvExporter;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Student Export — reuses the Export engine and the same filters as search.
 * CSV is implemented; XLSX/PDF are pluggable and reported unavailable for now.
 */
class StudentExportController extends BaseController
{
    public function export(Request $request, StudentService $service): StreamedResponse
    {
        $format = (string) $request->input('format', 'csv');

        $page = $service->list(array_merge($request->all(), ['per_page' => 1000]));

        $headings = ['Admission No', 'Name', 'Gender', 'Status', 'Class', 'Section', 'Guardian'];
        $rows = array_map(static function (Student $s): array {
            $current = $s->currentRecord;

            return [
                $s->admission_number,
                $s->name,
                $s->gender,
                $s->status?->value,
                $current?->schoolClass?->name,
                $current?->section?->name,
                $s->guardians->first()?->name,
            ];
        }, $page->items());

        $exporter = match ($format) {
            'csv' => new CsvExporter,
            default => throw BusinessRuleException::make(
                "Export format '{$format}' is not available yet.",
                'EXPORT_FORMAT_UNAVAILABLE',
            ),
        };

        return $exporter->download('students', $headings, $rows);
    }
}
