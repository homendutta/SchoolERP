<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Reports\Enums\ExportStatus;
use App\Modules\Reports\Jobs\ProcessExportJob;
use App\Modules\Reports\Models\ReportExport;
use App\Modules\Reports\Support\ExportResult;
use App\Modules\Reports\Support\ReportDefinition;
use App\Modules\Reports\Support\ReportRegistry;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\DomainException;

/**
 * Orchestrates report execution, export and print. It is the ONLY place these
 * flows live — every module runs/exports/prints through here. Reads only; every
 * export is audited.
 */
class RunReportService
{
    public function __construct(
        private readonly ReportRegistry $registry,
        private readonly ReportingEngine $engine,
        private readonly ExportEngine $exporter,
        private readonly PrintEngine $printer,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function run(User $user, string $key, array $params): array
    {
        $definition = $this->authorize($user, $key);

        return $this->engine->run($definition, $params);
    }

    /**
     * Export synchronously and return the rendered file (small/interactive exports).
     *
     * @param  array<string, mixed>  $params
     */
    public function export(User $user, string $key, array $params, string $format): ExportResult
    {
        $definition = $this->authorize($user, $key);
        $rows = $this->engine->rows($definition, $params);
        $result = $this->exporter->export($definition, $rows, $format);

        $export = ReportExport::query()->create([
            'school_id' => $params['school_id'] ?? $user->school_id,
            'report_key' => $key,
            'report_name' => $definition->name,
            'format' => $format,
            'status' => ExportStatus::Completed->value,
            'params' => $params,
            'row_count' => $result->rowCount,
            'requested_by' => $user->id,
            'completed_at' => now(),
        ]);

        $this->activity->record('reports.exported', "Exported {$definition->name} ({$format})", $export, [
            'rows' => $result->rowCount,
        ], (int) $export->school_id, 'reports');

        return $result;
    }

    /**
     * Queue an export (large exports). Returns the export record; the job renders it.
     *
     * @param  array<string, mixed>  $params
     */
    public function queueExport(User $user, string $key, array $params, string $format): ReportExport
    {
        $definition = $this->authorize($user, $key);

        $export = ReportExport::query()->create([
            'school_id' => $params['school_id'] ?? $user->school_id,
            'report_key' => $key,
            'report_name' => $definition->name,
            'format' => $format,
            'status' => ExportStatus::Queued->value,
            'params' => $params,
            'requested_by' => $user->id,
        ]);

        ProcessExportJob::dispatch($export->id);

        return $export;
    }

    /**
     * Render a print-ready HTML document (the one print/PDF layer).
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $options
     */
    public function print(User $user, string $key, array $params, array $options): string
    {
        $definition = $this->authorize($user, $key);
        $rows = $this->engine->rows($definition, $params);
        $html = $this->printer->render($definition, $rows, $options);

        $this->activity->record('reports.printed', "Printed {$definition->name}", null, [
            'report_key' => $key,
        ], (int) ($params['school_id'] ?? $user->school_id), 'reports');

        return $html;
    }

    private function authorize(User $user, string $key): ReportDefinition
    {
        $definition = $this->registry->get($key);
        if (method_exists($user, 'hasPermission') && ! $user->hasPermission($definition->permission)) {
            throw new DomainException('You are not authorized to run this report.', 403, 'REPORT_FORBIDDEN');
        }

        return $definition;
    }
}
