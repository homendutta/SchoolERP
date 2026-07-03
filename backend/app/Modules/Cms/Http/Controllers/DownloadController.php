<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Http\Requests\DownloadRequest;
use App\Modules\Cms\Http\Resources\SimpleResource;
use App\Modules\Cms\Services\DownloadService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class DownloadController extends BaseCrudController
{
    public function __construct(private readonly DownloadService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(DownloadRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(DownloadRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
