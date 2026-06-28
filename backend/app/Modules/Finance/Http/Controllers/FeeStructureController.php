<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\FeeStructureRequest;
use App\Modules\Finance\Http\Resources\FeeStructureResource;
use App\Modules\Finance\Services\FeeStructureService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class FeeStructureController extends BaseCrudController
{
    public function __construct(private readonly FeeStructureService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return FeeStructureResource::class;
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new FeeStructureResource($this->service->find($id)->load('items.feeMaster')->loadCount('items')));
    }

    public function store(FeeStructureRequest $request): JsonResponse
    {
        return $this->ok(new FeeStructureResource($this->service->create($request->validated())), 'Created.', 201);
    }

    public function update(FeeStructureRequest $request, int|string $id): JsonResponse
    {
        $model = $this->service->find($id);

        return $this->ok(new FeeStructureResource($this->service->update($model, $request->validated())), 'Updated.');
    }
}
