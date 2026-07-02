<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Actions\AssignAssetAction;
use App\Modules\Inventory\Http\Requests\AssignmentRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Modules\Inventory\Services\AssignmentEngine;
use App\Modules\Inventory\Services\AssignmentService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Asset assignments — historical; a return closes the record, never deletes. */
class AssignmentController extends BaseController
{
    public function __construct(
        private readonly AssignmentService $service,
        private readonly AssignmentEngine $engine,
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

    public function assign(AssignmentRequest $request, AssignAssetAction $action): JsonResponse
    {
        /** @var array{asset_id:int, target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null} $data */
        $data = $request->validated();

        return $this->ok(new SimpleResource($action->handle($data)->load('asset:id,asset_number')), 'Assigned.', 201);
    }

    public function returnAsset(int|string $id): JsonResponse
    {
        $assignment = $this->engine->returnAsset(AssetAssignment::query()->findOrFail($id));

        return $this->ok(new SimpleResource($assignment), 'Returned.');
    }
}
