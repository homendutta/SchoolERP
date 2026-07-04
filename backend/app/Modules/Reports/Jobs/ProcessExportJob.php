<?php

declare(strict_types=1);

namespace App\Modules\Reports\Jobs;

use App\Modules\Reports\Enums\ExportStatus;
use App\Modules\Reports\Models\ReportExport;
use App\Modules\Reports\Services\ExportEngine;
use App\Modules\Reports\Services\ReportingEngine;
use App\Modules\Reports\Support\ReportRegistry;
use App\Platform\Foundation\Audit\ActivityLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Queued rendering of a report export (large / scheduled exports never block the
 * request). Records completion / failure back onto the export row.
 */
class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $exportId) {}

    public function handle(ReportRegistry $registry, ReportingEngine $engine, ExportEngine $exporter, ActivityLogger $activity): void
    {
        $export = ReportExport::query()->find($this->exportId);
        if ($export === null || ! $registry->has($export->report_key)) {
            return;
        }

        try {
            $export->update(['status' => ExportStatus::Processing->value]);

            $definition = $registry->get($export->report_key);
            $rows = $engine->rows($definition, $export->params ?? []);
            $result = $exporter->export($definition, $rows, $export->format->value);

            $export->update([
                'status' => ExportStatus::Completed->value,
                'row_count' => $result->rowCount,
                'completed_at' => now(),
            ]);

            $activity->record('reports.exported', "Exported {$definition->name} ({$export->format->value})", $export, [
                'rows' => $result->rowCount, 'queued' => true,
            ], (int) $export->school_id, 'reports');
        } catch (Throwable $e) {
            $export->update(['status' => ExportStatus::Failed->value, 'error' => $e->getMessage()]);
        }
    }
}
