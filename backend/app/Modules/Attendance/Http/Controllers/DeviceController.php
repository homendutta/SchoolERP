<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Http\Requests\DeviceRequest;
use App\Modules\Attendance\Http\Resources\DeviceResource;
use App\Modules\Attendance\Services\DeviceService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class DeviceController extends BaseCrudController
{
    public function __construct(private readonly DeviceService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return DeviceResource::class;
    }

    public function store(DeviceRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(DeviceRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
