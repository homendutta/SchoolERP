<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Export\CsvExporter;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generic Export framework. CSV is implemented; XLSX/PDF drivers are pluggable
 * and reported as unavailable until added. No module-specific exports yet.
 *   POST /api/v1/admin/export   { format, filename, headings[], rows[][] }
 */
class ExportController extends BaseController
{
    public function export(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'format' => ['required', 'in:csv,xlsx,pdf'],
            'filename' => ['sometimes', 'string', 'max:100'],
            'headings' => ['required', 'array'],
            'rows' => ['required', 'array'],
        ]);

        $exporter = match ($data['format']) {
            'csv' => new CsvExporter,
            default => throw BusinessRuleException::make(
                "Export format '{$data['format']}' is not available yet.",
                'EXPORT_FORMAT_UNAVAILABLE',
            ),
        };

        return $exporter->download($data['filename'] ?? 'export', $data['headings'], $data['rows']);
    }
}
