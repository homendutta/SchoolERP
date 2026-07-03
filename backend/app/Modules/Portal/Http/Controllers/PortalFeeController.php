<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Modules\Portal\Http\Requests\PayRequest;
use App\Modules\Portal\Services\PortalContextService;
use App\Modules\Portal\Services\PortalDataService;
use App\Modules\Portal\Services\PortalPaymentService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Portal finance — fee details, history, receipts and online payment. All figures
 * come from the Finance module (source of truth); payment reuses the Finance
 * Payment Engine + Gateway abstraction. Teachers have no fee access.
 */
class PortalFeeController extends BaseController
{
    public function __construct(
        private readonly PortalContextService $context,
        private readonly PortalDataService $data,
        private readonly PortalPaymentService $payments,
    ) {}

    public function fees(Request $request): JsonResponse
    {
        $this->context->requireFeePayer($request->user());
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));

        return $this->ok($this->data->fees((int) $student->id));
    }

    public function history(Request $request): JsonResponse
    {
        $this->context->requireFeePayer($request->user());
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));

        return $this->ok($this->data->feeHistory((int) $student->id));
    }

    public function receipt(Request $request, int|string $id): JsonResponse
    {
        $this->context->requireFeePayer($request->user());

        return $this->ok($this->payments->receipt($request->user(), (int) $id));
    }

    public function gateways(Request $request): JsonResponse
    {
        $this->context->requireFeePayer($request->user());

        return $this->ok(['providers' => $this->payments->gatewayProviders()]);
    }

    /** Pay one or more children's fees in a single (atomic) transaction. */
    public function pay(PayRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->payments->pay(
            $request->user(),
            $validated['items'],
            $validated['gateway'] ?? null,
        );

        return $this->ok($result, 'Payment successful.', 201);
    }
}
