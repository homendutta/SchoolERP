<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Requests\PerformanceRequest;
use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Services\PerformanceService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class PerformanceController extends BaseCrudController
{
    public function __construct(private readonly PerformanceService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(PerformanceRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(PerformanceRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
