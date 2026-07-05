<?php

declare(strict_types=1);

namespace App\Modules\System\Http\Controllers;

use App\Modules\System\Services\HealthService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

/**
 * Public liveness/readiness probe (no auth) for load balancers + uptime monitors.
 * Returns only the overall score/status — never component details or config.
 */
class PublicHealthController extends BaseController
{
    public function __construct(private readonly HealthService $health) {}

    public function ping(): JsonResponse
    {
        $health = $this->health->check();

        return $this->ok([
            'status' => $health['status'],
            'score' => $health['score'],
            'time' => now()->toIso8601String(),
        ]);
    }
}
