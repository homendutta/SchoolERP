<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Http\Requests\CategoryRequest;
use App\Modules\Documents\Http\Resources\SimpleResource;
use App\Modules\Documents\Services\CategoryService;
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
