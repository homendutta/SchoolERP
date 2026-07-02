<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Services\HostelDashboardService;
use App\Modules\Hostel\Services\OccupancyService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostelDashboardController extends BaseController
{
    public function __construct(
        private readonly HostelDashboardService $service,
        private readonly OccupancyService $occupancy,
    ) {}

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate(['school_id' => ['nullable', 'integer']]);

        return $this->ok($this->service->overview(isset($validated['school_id']) ? (int) $validated['school_id'] : null));
    }

    /** Occupancy summary (optionally for one hostel). */
    public function occupancy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => ['nullable', 'integer'],
            'hostel_id' => ['nullable', 'integer'],
        ]);

        return $this->ok($this->occupancy->summary(
            isset($validated['school_id']) ? (int) $validated['school_id'] : null,
            isset($validated['hostel_id']) ? (int) $validated['hostel_id'] : null,
        ));
    }
}
