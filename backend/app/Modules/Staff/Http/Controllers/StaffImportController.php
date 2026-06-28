<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Administration\Services\ImportService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Staff Import — Upload → Validate → Preview → Import → Summary, bound to the
 * 'staff' importer.
 */
class StaffImportController extends BaseController
{
    private const KEY = 'staff';

    public function __construct(private readonly ImportService $service) {}

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);
        $rows = $this->service->parseCsv($request->file('file'));
        $errors = $this->service->validate(self::KEY, $rows);

        return $this->ok([
            'count' => count($rows),
            'headings' => $rows === [] ? [] : array_keys($rows[0]),
            'preview' => array_slice($rows, 0, 10),
            'rows' => $rows,
            'valid' => $errors === [],
            'errors' => $errors,
        ], 'File parsed.');
    }

    public function validateRows(Request $request): JsonResponse
    {
        $data = $request->validate(['rows' => ['required', 'array']]);
        $errors = $this->service->validate(self::KEY, $data['rows']);

        return $this->ok(['valid' => $errors === [], 'errors' => $errors]);
    }

    public function execute(Request $request): JsonResponse
    {
        $data = $request->validate(['rows' => ['required', 'array']]);

        return $this->ok($this->service->execute(self::KEY, $data['rows']), 'Import executed.');
    }
}
