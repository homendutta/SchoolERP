<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Services\ImportService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generic Import framework endpoints (modules plug in their importers):
 *   POST /api/v1/admin/import/upload    parse a CSV -> rows + preview
 *   POST /api/v1/admin/import/validate  validate rows for an importer key
 *   POST /api/v1/admin/import/execute   persist rows -> summary
 */
class ImportController extends BaseController
{
    public function __construct(private readonly ImportService $service) {}

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);
        $rows = $this->service->parseCsv($request->file('file'));

        return $this->ok([
            'count' => count($rows),
            'headings' => $rows === [] ? [] : array_keys($rows[0]),
            'preview' => array_slice($rows, 0, 10),
            'rows' => $rows,
        ], 'File parsed.');
    }

    public function validateRows(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'rows' => ['required', 'array'],
        ]);

        $errors = $this->service->validate($data['key'], $data['rows']);

        return $this->ok([
            'valid' => $errors === [],
            'errors' => $errors,
        ]);
    }

    public function execute(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'rows' => ['required', 'array'],
        ]);

        return $this->ok($this->service->execute($data['key'], $data['rows']), 'Import executed.');
    }
}
