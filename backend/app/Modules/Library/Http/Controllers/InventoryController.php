<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Enums\InventoryStatus;
use App\Modules\Library\Http\Requests\InventoryRequest;
use App\Modules\Library\Http\Resources\SimpleResource;
use App\Modules\Library\Models\Copy;
use App\Modules\Library\Models\InventoryCheck;
use App\Modules\Library\Services\InventoryService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends BaseController
{
    public function __construct(private readonly InventoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $checks = InventoryCheck::query()
            ->when($request->has('school_id'), fn ($q) => $q->where('school_id', (int) $request->integer('school_id')))
            ->when($request->has('copy_id'), fn ($q) => $q->where('copy_id', (int) $request->integer('copy_id')))
            ->latest('id')->paginate(25);

        return $this->ok(SimpleResource::collection($checks), null, 200, [
            'total' => $checks->total(),
            'per_page' => $checks->perPage(),
            'current_page' => $checks->currentPage(),
            'last_page' => $checks->lastPage(),
        ]);
    }

    /** Record a verification for a copy. */
    public function store(InventoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $copy = Copy::query()->findOrFail($data['copy_id']);
        $check = $this->service->record($copy, InventoryStatus::from((string) $data['status']), $data['notes'] ?? null, Auth::id());

        return $this->ok(new SimpleResource($check), 'Inventory recorded.', 201);
    }

    /** Aggregate inventory report (latest status per copy). */
    public function report(Request $request): JsonResponse
    {
        return $this->ok($this->service->report((int) $request->integer('school_id')));
    }
}
