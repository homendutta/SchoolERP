<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Http\Requests\WorkflowStepRequest;
use App\Modules\Admissions\Http\Resources\WorkflowStepResource;
use App\Modules\Admissions\Services\WorkflowStepService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class WorkflowStepController extends BaseCrudController
{
    public function __construct(private readonly WorkflowStepService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return WorkflowStepResource::class;
    }

    public function store(WorkflowStepRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(WorkflowStepRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
