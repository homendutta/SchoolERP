<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Enums\DisposalMethod;
use App\Modules\Inventory\Http\Requests\DisposalRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Services\DisposalService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisposalController extends BaseController
{
    public function __construct(private readonly DisposalService $service) {}

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

    public function store(DisposalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $asset = Asset::query()->findOrFail($data['asset_id']);
        $disposal = $this->service->dispose($asset, DisposalMethod::from((string) $data['method']), [
            'reason' => $data['reason'] ?? null,
            'disposal_date' => $data['disposal_date'] ?? null,
            'value' => isset($data['value']) ? (float) $data['value'] : null,
        ]);

        return $this->ok(new SimpleResource($disposal), 'Asset disposed.', 201);
    }
}
