<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Controllers;

use App\Modules\Reports\Enums\ReportFormat;
use App\Modules\Reports\Http\Resources\SimpleResource;
use App\Modules\Reports\Services\RunReportService;
use App\Modules\Reports\Support\ReportRegistry;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/** Catalog + execution + export + print — the one reporting/printing surface. */
class ReportController extends BaseController
{
    public function __construct(
        private readonly ReportRegistry $registry,
        private readonly RunReportService $runner,
    ) {}

    /** The report catalog (code-registered definitions). */
    public function catalog(): JsonResponse
    {
        return $this->ok(array_map(fn ($d) => $d->toCatalogArray(), $this->registry->all()));
    }

    /** Execute a report (filters/sort/group/paginate/totals). */
    public function run(Request $request): JsonResponse
    {
        $v = $request->validate([
            'report_key' => ['required', 'string'],
            'school_id' => ['nullable', 'integer'],
            'filter' => ['nullable', 'array'],
            'sort' => ['nullable', 'string'],
            'group_by' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);
        $v['school_id'] = $v['school_id'] ?? $request->user()->school_id;

        return $this->ok($this->runner->run($request->user(), $v['report_key'], $v));
    }

    /** Export a report (CSV/Excel). Large exports may be queued. */
    public function export(Request $request): Response|JsonResponse
    {
        $v = $request->validate([
            'report_key' => ['required', 'string'],
            'format' => ['required', Rule::in([ReportFormat::Csv->value, ReportFormat::Xlsx->value])],
            'school_id' => ['nullable', 'integer'],
            'filter' => ['nullable', 'array'],
            'sort' => ['nullable', 'string'],
            'queue' => ['nullable', 'boolean'],
        ]);
        $v['school_id'] = $v['school_id'] ?? $request->user()->school_id;

        if (! empty($v['queue'])) {
            $export = $this->runner->queueExport($request->user(), $v['report_key'], $v, $v['format']);

            return $this->ok(new SimpleResource($export), 'Export queued.', 202);
        }

        $result = $this->runner->export($request->user(), $v['report_key'], $v, $v['format']);

        return response($result->content, 200, [
            'Content-Type' => $result->mime,
            'Content-Disposition' => 'attachment; filename="'.$result->filename.'"',
        ]);
    }

    /** Render a print-ready HTML document (the one print/PDF layer). */
    public function print(Request $request): Response
    {
        $v = $request->validate([
            'report_key' => ['required', 'string'],
            'school_id' => ['nullable', 'integer'],
            'filter' => ['nullable', 'array'],
            'sort' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
        ]);
        $v['school_id'] = $v['school_id'] ?? $request->user()->school_id;

        $html = $this->runner->print($request->user(), $v['report_key'], $v, is_array($v['options'] ?? null) ? $v['options'] : []);

        return response($html, 200, ['Content-Type' => 'text/html']);
    }
}
