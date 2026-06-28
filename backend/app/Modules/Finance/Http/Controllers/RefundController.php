<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Actions\RefundAction;
use App\Modules\Finance\Http\Requests\RefundRequest;
use App\Modules\Finance\Http\Resources\RefundResource;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Services\RefundReadService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Refunds never delete payments — they create independent refund records. */
class RefundController extends BaseController
{
    public function __construct(private readonly RefundReadService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(RefundResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function store(RefundRequest $request, RefundAction $action): JsonResponse
    {
        $data = $request->validated();
        $payment = Payment::query()->findOrFail($data['payment_id']);
        /** @var array{amount:float, type?:string, reason?:string|null} $payload */
        $payload = $data;
        $refund = $action->handle($payment, $payload);

        return $this->ok(new RefundResource($refund), 'Refund issued.', 201);
    }
}
