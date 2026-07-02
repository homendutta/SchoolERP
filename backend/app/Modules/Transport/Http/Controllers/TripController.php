<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Http\Requests\TripRequest;
use App\Modules\Transport\Http\Resources\SimpleResource;
use App\Modules\Transport\Services\TripService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class TripController extends BaseCrudController
{
    public function __construct(private readonly TripService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(TripRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TripRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
