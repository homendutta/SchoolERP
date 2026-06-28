<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Http\Resources\AttendanceRecordResource;
use App\Modules\Attendance\Services\AttendanceRecordService;
use App\Modules\Staff\Models\Staff;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Staff attendance — reads the unified attendance table scoped to Staff owners
 * (daily / department / designation).
 */
class StaffAttendanceController extends BaseController
{
    public function __construct(private readonly AttendanceRecordService $service) {}

    public function index(Request $request): JsonResponse
    {
        $params = $request->all();
        $params['filter'] = array_merge((array) ($params['filter'] ?? []), ['owner_type' => Staff::class]);

        $page = $this->service->list($params);

        return $this->ok(AttendanceRecordResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }
}
