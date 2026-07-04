<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Models\ReportExport;
use App\Modules\Reports\Models\ReportSchedule;
use Illuminate\Database\Eloquent\Builder;

class ReportDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $exports = fn (): Builder => ReportExport::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        return [
            'widgets' => [
                'scheduled_reports' => ReportSchedule::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'active')->count(),
                'recent_exports' => (clone $exports())->where('created_at', '>=', now()->subDays(7))->count(),
                'export_queue' => (clone $exports())->whereIn('status', ['queued', 'processing'])->count(),
                'failed_reports' => (clone $exports())->where('status', 'failed')->count(),
                'total_exports' => (clone $exports())->count(),
            ],
            'charts' => [
                'most_used_reports' => (clone $exports())->get(['report_key', 'report_name'])
                    ->groupBy('report_key')
                    ->map(fn ($g) => ['label' => (string) ($g->first()->report_name ?? $g->first()->report_key), 'count' => $g->count()])
                    ->sortByDesc('count')->values()->take(10)->all(),
                'export_trend' => (clone $exports())->get(['created_at'])
                    ->groupBy(fn ($e) => optional($e->created_at)->format('Y-m-d') ?? 'n/a')
                    ->map(fn ($g, $d) => ['label' => (string) $d, 'count' => $g->count()])
                    ->sortKeys()->values()->take(-14)->all(),
                'format_distribution' => (clone $exports())->get(['format'])
                    ->groupBy(fn ($e) => $e->format->value)
                    ->map(fn ($g, $f) => ['label' => strtoupper((string) $f), 'count' => $g->count()])
                    ->values()->all(),
            ],
        ];
    }
}
