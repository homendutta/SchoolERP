<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\ModelRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Services\ModelService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ModelController extends BaseCrudController
{
    public function __construct(private readonly ModelService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(ModelRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ModelRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
