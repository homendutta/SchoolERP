<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Requests\SeparationRequest;
use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Services\SeparationService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SeparationController extends BaseCrudController
{
    public function __construct(private readonly SeparationService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(SeparationRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SeparationRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
