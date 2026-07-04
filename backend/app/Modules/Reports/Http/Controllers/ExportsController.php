<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Controllers;

use App\Modules\Reports\Http\Resources\SimpleResource;
use App\Modules\Reports\Services\ExportHistoryService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Export history / queue (read-only). */
class ExportsController extends BaseController
{
    public function __construct(private readonly ExportHistoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(SimpleResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new SimpleResource($this->service->find($id)));
    }
}
