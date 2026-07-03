<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Services\PayrollDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollDashboardController extends BaseController
{
    public function __construct(private readonly PayrollDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->integer('school_id') ?: null;

        return $this->ok($this->service->overview($schoolId));
    }
}
