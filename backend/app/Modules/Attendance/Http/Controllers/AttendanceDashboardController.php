<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Services\AttendanceDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceDashboardController extends BaseController
{
    public function __construct(private readonly AttendanceDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $type = $request->input('type') === 'staff' ? 'staff' : 'student';
        $schoolId = $request->filled('school_id') ? $request->integer('school_id') : null;

        return $this->ok($this->service->overview($type, $schoolId, $request->all()));
    }
}
