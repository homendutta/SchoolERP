<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Controllers;

use App\Modules\Reports\Services\ReportDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportDashboardController extends BaseController
{
    public function __construct(private readonly ReportDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->integer('school_id') ?: null;

        return $this->ok($this->service->overview($schoolId));
    }
}
