<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Http\Requests\RouteRequest;
use App\Modules\Transport\Http\Resources\SimpleResource;
use App\Modules\Transport\Services\RouteService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class RouteController extends BaseCrudController
{
    public function __construct(private readonly RouteService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(RouteRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(RouteRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
