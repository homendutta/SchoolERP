<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Services\CmsDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsDashboardController extends BaseController
{
    public function __construct(private readonly CmsDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->integer('school_id') ?: null;

        return $this->ok($this->service->overview($schoolId));
    }
}
