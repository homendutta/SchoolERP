<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\FeeCategoryRequest;
use App\Modules\Finance\Http\Resources\FeeCategoryResource;
use App\Modules\Finance\Services\FeeCategoryService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class FeeCategoryController extends BaseCrudController
{
    public function __construct(private readonly FeeCategoryService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return FeeCategoryResource::class;
    }

    public function store(FeeCategoryRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(FeeCategoryRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
