<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Models\IntegrationLog;
use App\Modules\Integrations\Models\Provider;
use App\Modules\Integrations\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Builder;

class MonitoringDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $logs = fn (): Builder => IntegrationLog::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $providers = fn (): Builder => Provider::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $total = (clone $logs())->count();
        $success = (clone $logs())->where('status', 'success')->count();

        return [
            'widgets' => [
                'providers' => (clone $providers())->count(),
                'enabled_providers' => (clone $providers())->where('status', 'enabled')->count(),
                'failed_requests' => (clone $logs())->where('status', 'failure')->count(),
                'retry_queue' => WebhookDelivery::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'pending')->count(),
                'success_rate' => $total > 0 ? round($success / $total * 100, 1) : 100,
                'avg_response_ms' => (int) round((float) (clone $logs())->avg('duration_ms')),
            ],
            'charts' => [
                'provider_status' => (clone $providers())->get(['health'])
                    ->groupBy(fn ($p) => $p->health->value)
                    ->map(fn ($g, $h) => ['label' => ucfirst((string) $h), 'count' => $g->count()])->values()->all(),
                'requests_by_provider' => (clone $logs())->get(['provider_code'])
                    ->groupBy(fn ($l) => $l->provider_code ?? 'unknown')
                    ->map(fn ($g, $c) => ['label' => (string) $c, 'count' => $g->count()])
                    ->sortByDesc('count')->values()->take(10)->all(),
                'request_trend' => (clone $logs())->get(['created_at'])
                    ->groupBy(fn ($l) => optional($l->created_at)->format('Y-m-d') ?? 'n/a')
                    ->map(fn ($g, $d) => ['label' => (string) $d, 'count' => $g->count()])
                    ->sortKeys()->values()->take(-14)->all(),
            ],
        ];
    }
}
