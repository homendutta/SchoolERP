<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Services\HrDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrDashboardController extends BaseController
{
    public function __construct(private readonly HrDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->integer('school_id') ?: null;

        return $this->ok($this->service->overview($schoolId));
    }
}
