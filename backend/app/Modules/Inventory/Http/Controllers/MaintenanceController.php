<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\MaintenanceRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Services\MaintenanceService;
use App\Platform\Foundation\Maintenance\MaintenanceEngine;
use App\Platform\Foundation\Maintenance\Models\MaintenanceRequest as MaintenanceRecord;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Asset maintenance — consumes the reusable Platform Maintenance Engine. */
class MaintenanceController extends BaseController
{
    public function __construct(
        private readonly MaintenanceService $service,
        private readonly MaintenanceEngine $engine,
    ) {}

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

    public function store(MaintenanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $asset = Asset::query()->findOrFail($data['asset_id']);

        return $this->ok(new SimpleResource($this->engine->schedule($asset, $data)), 'Maintenance scheduled.', 201);
    }

    public function update(MaintenanceRequest $request, int|string $id): JsonResponse
    {
        $record = MaintenanceRecord::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->engine->update($record, $request->validated())), 'Maintenance updated.');
    }

    public function destroy(int|string $id): JsonResponse
    {
        MaintenanceRecord::query()->findOrFail($id)->delete();

        return $this->ok(null, 'Deleted.');
    }
}
