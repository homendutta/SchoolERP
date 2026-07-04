<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers;

use App\Modules\Integrations\Http\Resources\SimpleResource;
use App\Modules\Integrations\Models\IntegrationEvent;
use App\Modules\Integrations\Models\IntegrationLog;
use App\Modules\Integrations\Services\IntegrationService;
use App\Modules\Integrations\Services\MonitoringDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Monitoring: dashboard, registered adapters, event bus + request logs. */
class MonitoringController extends BaseController
{
    public function __construct(
        private readonly MonitoringDashboardService $dashboard,
        private readonly IntegrationService $integration,
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        return $this->ok($this->dashboard->overview($request->integer('school_id') ?: null));
    }

    /** The registered adapter catalog (discovery). */
    public function adapters(): JsonResponse
    {
        return $this->ok($this->integration->adapters());
    }

    public function events(Request $request): JsonResponse
    {
        $rows = IntegrationEvent::query()
            ->when($request->integer('school_id'), fn ($q) => $q->where('school_id', $request->integer('school_id')))
            ->latest('id')->paginate((int) ($request->integer('per_page') ?: 50));

        return $this->ok(SimpleResource::collection($rows), null, 200, [
            'total' => $rows->total(), 'per_page' => $rows->perPage(), 'current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(),
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $rows = IntegrationLog::query()
            ->when($request->integer('school_id'), fn ($q) => $q->where('school_id', $request->integer('school_id')))
            ->when($request->string('status')->isNotEmpty(), fn ($q) => $q->where('status', (string) $request->string('status')))
            ->latest('id')->paginate((int) ($request->integer('per_page') ?: 50));

        return $this->ok(SimpleResource::collection($rows), null, 200, [
            'total' => $rows->total(), 'per_page' => $rows->perPage(), 'current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(),
        ]);
    }
}
