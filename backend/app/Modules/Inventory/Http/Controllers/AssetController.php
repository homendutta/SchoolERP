<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Http\Requests\AssetRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\LifecycleEvent;
use App\Modules\Inventory\Services\AssetLifecycleService;
use App\Modules\Inventory\Services\AssetService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends BaseCrudController
{
    public function __construct(
        private readonly AssetService $service,
        private readonly AssetLifecycleService $lifecycle,
    ) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(AssetRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(AssetRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Move an asset to a new lifecycle state (Audit + Timeline written). */
    public function transition(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'to_status' => ['required', Rule::in(AssetStatus::values())],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $asset = Asset::query()->findOrFail($id);
        $this->lifecycle->transition($asset, AssetStatus::from((string) $validated['to_status']), $validated['note'] ?? null);

        return $this->ok(new SimpleResource($asset->fresh()), 'Lifecycle updated.');
    }

    /** The asset's lifecycle Timeline (immutable history). */
    public function lifecycle(int|string $id): JsonResponse
    {
        $events = LifecycleEvent::query()->where('asset_id', $id)->orderBy('id')->get();

        return $this->ok(SimpleResource::collection($events));
    }
}
