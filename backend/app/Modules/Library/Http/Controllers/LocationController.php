<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Requests\LocationRequest;
use App\Modules\Library\Http\Resources\SimpleResource;
use App\Modules\Library\Services\LocationService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class LocationController extends BaseCrudController
{
    public function __construct(private readonly LocationService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(LocationRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(LocationRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
