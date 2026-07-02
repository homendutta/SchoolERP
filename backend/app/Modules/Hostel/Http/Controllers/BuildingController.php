<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Http\Requests\BuildingRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Services\BuildingService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class BuildingController extends BaseCrudController
{
    public function __construct(private readonly BuildingService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(BuildingRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(BuildingRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
