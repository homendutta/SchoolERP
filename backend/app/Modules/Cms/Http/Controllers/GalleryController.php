<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Http\Requests\GalleryRequest;
use App\Modules\Cms\Http\Resources\SimpleResource;
use App\Modules\Cms\Services\GalleryService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class GalleryController extends BaseCrudController
{
    public function __construct(private readonly GalleryService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(GalleryRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(GalleryRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
