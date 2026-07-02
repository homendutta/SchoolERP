<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Enums\MovementType;
use App\Modules\Inventory\Http\Requests\MovementRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Models\Consumable;
use App\Modules\Inventory\Services\MovementService;
use App\Modules\Inventory\Services\StockService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Append-only stock movements. Quantities are never overwritten. */
class MovementController extends BaseController
{
    public function __construct(
        private readonly MovementService $service,
        private readonly StockService $stock,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all() + ['sort' => 'id']);

        return $this->ok(SimpleResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function store(MovementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $consumable = Consumable::query()->findOrFail($data['consumable_id']);
        $movement = $this->stock->record($consumable, MovementType::from((string) $data['type']), (float) $data['quantity'], [
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->ok(new SimpleResource($movement), 'Stock movement recorded.', 201);
    }
}
