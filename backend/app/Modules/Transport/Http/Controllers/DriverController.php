<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Http\Requests\VehicleStaffRequest;
use App\Modules\Transport\Http\Resources\SimpleResource;
use App\Modules\Transport\Services\VehicleStaffService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/**
 * Driver / attendant assignments to vehicles. Staff come from Staff Management —
 * this never creates another employee system.
 */
class DriverController extends BaseCrudController
{
    public function __construct(private readonly VehicleStaffService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(VehicleStaffRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(VehicleStaffRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
