<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Controllers;

use App\Modules\Reports\Enums\ReportFormat;
use App\Modules\Reports\Enums\ScheduleFrequency;
use App\Modules\Reports\Http\Resources\SimpleResource;
use App\Modules\Reports\Models\ReportSchedule;
use App\Modules\Reports\Services\ScheduleService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchedulesController extends BaseCrudController
{
    public function __construct(private readonly ScheduleService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request): array
    {
        $required = $request->isMethod('post') ? 'required' : 'sometimes';

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            'report_key' => [$required, 'string', 'max:100'],
            'name' => [$required, 'string', 'max:255'],
            'frequency' => [$required, Rule::in(ScheduleFrequency::values())],
            'format' => ['sometimes', Rule::in(ReportFormat::values())],
            'filters' => ['nullable', 'array'],
            'recipients' => ['nullable', 'array'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules($request));
        $data['created_by'] = $request->user()->id;

        return $this->created($data);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validate($this->rules($request)));
    }

    /** Run a schedule now (queues the export + notifies recipients via Communication). */
    public function run(Request $request, int|string $id): JsonResponse
    {
        $schedule = ReportSchedule::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->service->runNow($request->user(), $schedule)), 'Schedule queued.');
    }
}
