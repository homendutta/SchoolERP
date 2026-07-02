<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\CategoryRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Services\CategoryService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseCrudController
{
    public function __construct(private readonly CategoryService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(CategoryRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
