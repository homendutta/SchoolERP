<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Requests\PublisherRequest;
use App\Modules\Library\Http\Resources\SimpleResource;
use App\Modules\Library\Services\PublisherService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class PublisherController extends BaseCrudController
{
    public function __construct(private readonly PublisherService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(PublisherRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(PublisherRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
