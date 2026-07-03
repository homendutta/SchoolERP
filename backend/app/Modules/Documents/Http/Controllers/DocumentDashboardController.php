<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Services\DocumentDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentDashboardController extends BaseController
{
    public function __construct(private readonly DocumentDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $schoolId = $request->integer('school_id') ?: null;

        return $this->ok($this->service->overview($schoolId));
    }
}
