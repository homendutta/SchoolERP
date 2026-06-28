<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Administration\Services\ImportService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Student Import — Upload → Validate → Preview → Import → Summary, bound to the
 * 'students' importer (migration mode). The only non-admission path to create a
 * Student.
 */
class StudentImportController extends BaseController
{
    private const KEY = 'students';

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
