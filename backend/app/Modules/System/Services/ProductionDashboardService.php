<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The production/operations dashboard: overall health, queue status, failed jobs,
 * scheduled jobs, storage/cache usage, active sessions, integration health and API
 * performance — the single operator view of the running system.
 */
class ProductionDashboardService
{
    public function __construct(
        private readonly HealthService $health,
        private readonly DiagnosticsService $diagnostics,
        private readonly FailedJobsService $failedJobs,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $health = $this->health->check();
        $disk = $this->diagnostics->info()['disk'];

        return [
            'health' => ['score' => $health['score'], 'status' => $health['status'], 'components' => $health['components']],
            'widgets' => [
                'overall_health' => $health['score'],
                'queue_pending' => $this->tableCount('integration_webhook_deliveries', ['status' => 'pending'])
                    + $this->tableCount('report_exports', ['status' => 'queued']),
                'failed_jobs' => $this->failedJobs->count(),
                'scheduled_jobs' => $this->tableCount('report_schedules', ['status' => 'active']),
                'storage_used_percent' => $disk['used_percent'],
                'active_sessions' => $this->tableCount('personal_access_tokens'),
                'integration_providers' => $this->tableCount('integration_providers', ['status' => 'enabled']),
                'api_avg_ms' => $this->avg('integration_logs', 'duration_ms'),
            ],
            'cache' => ['driver' => (string) config('cache.default')],
            'queue' => ['driver' => (string) config('queue.default')],
        ];
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function tableCount(string $table, array $where = []): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }
        $q = DB::table($table);
        foreach ($where as $col => $val) {
            $q->where($col, $val);
        }

        return (int) $q->count();
    }

    private function avg(string $table, string $column): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) round((float) DB::table($table)->avg($column));
    }
}
