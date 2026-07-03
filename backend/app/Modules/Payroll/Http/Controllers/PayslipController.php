<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Enums\SettlementStatus;
use App\Modules\Payroll\Http\Requests\SettlementRequest;
use App\Modules\Payroll\Http\Resources\PayslipResource;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Services\PayslipService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Payslips — structured data (no PDF); QR via the Identity Platform. */
class PayslipController extends BaseController
{
    public function __construct(private readonly PayslipService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(PayslipResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new PayslipResource($this->service->find($id)));
    }

    /** Record the payroll-side settlement status (Finance records the actual payment). */
    public function settle(SettlementRequest $request, int|string $id): JsonResponse
    {
        $payslip = Payslip::query()->findOrFail($id);
        $status = SettlementStatus::from((string) $request->validated()['settlement_status']);

        return $this->ok(new PayslipResource($this->service->settle($payslip, $status)->load([
            'lines', 'employee.identity',
        ])), 'Settlement recorded.');
    }
}
