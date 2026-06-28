<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Http\Requests\ExperienceRequest;
use App\Modules\Staff\Http\Resources\ExperienceResource;
use App\Modules\Staff\Services\StaffExperienceService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ExperienceController extends BaseCrudController
{
    public function __construct(private readonly StaffExperienceService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExperienceResource::class;
    }

    public function store(ExperienceRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ExperienceRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
