<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Http\Requests\NewsRequest;
use App\Modules\Cms\Http\Resources\SimpleResource;
use App\Modules\Cms\Services\NewsService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class NewsController extends BaseCrudController
{
    public function __construct(private readonly NewsService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(NewsRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(NewsRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
