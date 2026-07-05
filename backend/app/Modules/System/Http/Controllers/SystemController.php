<?php

declare(strict_types=1);

namespace App\Modules\System\Http\Controllers;

use App\Modules\System\Services\ConfigValidator;
use App\Modules\System\Services\DiagnosticsService;
use App\Modules\System\Services\HealthService;
use App\Modules\System\Services\ProductionDashboardService;
use App\Modules\System\Services\SystemLogService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Production/operations surface: dashboard, health, diagnostics, config, logs. */
class SystemController extends BaseController
{
    public function __construct(
        private readonly ProductionDashboardService $dashboard,
        private readonly HealthService $health,
        private readonly DiagnosticsService $diagnostics,
        private readonly ConfigValidator $config,
        private readonly SystemLogService $logs,
    ) {}

    public function dashboard(): JsonResponse
    {
        return $this->ok($this->dashboard->overview());
    }

    public function health(): JsonResponse
    {
        return $this->ok($this->health->check());
    }

    public function diagnostics(): JsonResponse
    {
        return $this->ok($this->diagnostics->info());
    }

    public function config(): JsonResponse
    {
        return $this->ok($this->config->validate());
    }

    public function logs(Request $request): JsonResponse
    {
        $page = $this->logs->list($request->all());

        return $this->ok($page->items(), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }
}
