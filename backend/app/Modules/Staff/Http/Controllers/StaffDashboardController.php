<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Services\StaffDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffDashboardController extends BaseController
{
    public function __construct(private readonly StaffDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->filled('school_id') ? $request->integer('school_id') : null;

        return $this->ok($this->service->overview($schoolId));
    }
}
