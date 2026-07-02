<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Http\Requests\VehicleRequest;
use App\Modules\Transport\Http\Resources\SimpleResource;
use App\Modules\Transport\Services\VehicleService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class VehicleController extends BaseCrudController
{
    public function __construct(private readonly VehicleService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(VehicleRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(VehicleRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
