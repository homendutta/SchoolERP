<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Actions\TransferAssetAction;
use App\Modules\Inventory\Http\Requests\AssignmentRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Services\TransferService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends BaseController
{
    public function __construct(private readonly TransferService $service) {}

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

    public function transfer(AssignmentRequest $request, TransferAssetAction $action): JsonResponse
    {
        /** @var array{asset_id:int, target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null, reason?:string|null, transfer_type?:string|null} $data */
        $data = $request->validated();

        return $this->ok(new SimpleResource($action->handle($data)->load('asset:id,asset_number')), 'Transferred.', 201);
    }
}
