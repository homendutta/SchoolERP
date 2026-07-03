<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Requests\ShiftRequest;
use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Services\ShiftService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ShiftController extends BaseCrudController
{
    public function __construct(private readonly ShiftService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(ShiftRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ShiftRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
