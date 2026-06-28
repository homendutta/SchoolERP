<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Services\AdmissionDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionDashboardController extends BaseController
{
    public function __construct(private readonly AdmissionDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->filled('school_id') ? $request->integer('school_id') : null;

        return $this->ok($this->service->overview($schoolId));
    }
}
