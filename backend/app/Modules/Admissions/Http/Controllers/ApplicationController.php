<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Http\Requests\ApplicationRequest;
use App\Modules\Admissions\Http\Resources\ApplicationResource;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Services\ApplicationService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ApplicationController extends BaseCrudController
{
    public function __construct(private readonly ApplicationService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ApplicationResource::class;
    }

    public function store(ApplicationRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ApplicationRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Submit a draft application for verification/approval. */
    public function submit(int|string $id): JsonResponse
    {
        /** @var AdmissionApplication $application */
        $application = $this->service->find($id);

        return $this->ok(
            new ApplicationResource($this->service->submit($application)),
            'Application submitted.',
        );
    }
}
