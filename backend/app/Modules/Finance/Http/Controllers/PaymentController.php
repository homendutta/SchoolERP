<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Actions\RecordPaymentAction;
use App\Modules\Finance\Http\Requests\PaymentRequest;
use App\Modules\Finance\Http\Resources\PaymentResource;
use App\Modules\Finance\Services\PaymentService;
use App\Modules\Finance\Services\ReceiptService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function __construct(
        private readonly PaymentService $service,
        private readonly ReceiptService $receipts,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(PaymentResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new PaymentResource($this->service->find($id)->load(['student:id,name,admission_number', 'method:id,label', 'allocations'])));
    }

    public function store(PaymentRequest $request, RecordPaymentAction $action): JsonResponse
    {
        /** @var array{school_id:int, student_id:int, amount:float, payment_method_id?:int|null, paid_on?:string|null, reference?:string|null, notes?:string|null, gateway?:string|null, allocations?:array<int, array{student_fee_item_id:int, amount:float}>} $payload */
        $payload = $request->validated();
        $payment = $action->handle($payload);

        return $this->ok(new PaymentResource($payment->load('allocations')), 'Payment recorded.', 201);
    }

    /** Reusable receipt data (no PDF rendering). */
    public function receipt(int|string $id): JsonResponse
    {
        return $this->ok($this->receipts->forPayment((int) $id));
    }
}
