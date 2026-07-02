<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Http\Requests\MaintenanceRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Services\MaintenanceService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class MaintenanceController extends BaseCrudController
{
    public function __construct(private readonly MaintenanceService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(MaintenanceRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(MaintenanceRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
