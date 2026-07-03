<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Http\Requests\EventRequest;
use App\Modules\Cms\Http\Resources\SimpleResource;
use App\Modules\Cms\Services\EventService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class EventController extends BaseCrudController
{
    public function __construct(private readonly EventService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(EventRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(EventRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
