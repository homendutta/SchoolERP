<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Actions\AdjustmentAction;
use App\Modules\Finance\Http\Requests\AdjustmentRequest;
use App\Modules\Finance\Http\Resources\AdjustmentResource;
use App\Modules\Finance\Services\AdjustmentReadService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Adjustments are independent records (credit/debit note, waiver, manual). */
class AdjustmentController extends BaseController
{
    public function __construct(private readonly AdjustmentReadService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(AdjustmentResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function store(AdjustmentRequest $request, AdjustmentAction $action): JsonResponse
    {
        /** @var array{school_id:int, student_id:int, type:string, amount:float, reason?:string|null, student_fee_id?:int|null} $payload */
        $payload = $request->validated();

        return $this->ok(new AdjustmentResource($action->handle($payload)), 'Adjustment created.', 201);
    }
}
