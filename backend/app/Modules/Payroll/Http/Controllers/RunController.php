<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Http\Requests\RunRequest;
use App\Modules\Payroll\Http\Resources\SimpleResource;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Services\PayrollEngine;
use App\Modules\Payroll\Services\PayrollRunService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/** Payroll runs — draft/create here; the engine processes and locks. */
class RunController extends BaseCrudController
{
    public function __construct(
        private readonly PayrollRunService $service,
        private readonly PayrollEngine $engine,
    ) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(RunRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(RunRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Process (idempotent) — generates payslips for every eligible employee. */
    public function process(int|string $id): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->engine->processRun($run)), 'Payroll processed.');
    }

    /** Lock the run — it becomes immutable; corrections require a new run. */
    public function lock(int|string $id): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->engine->lockRun($run)), 'Payroll locked.');
    }
}
