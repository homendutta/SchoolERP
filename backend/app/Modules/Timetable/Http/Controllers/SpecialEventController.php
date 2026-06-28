<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Http\Requests\SpecialEventRequest;
use App\Modules\Timetable\Http\Resources\SpecialEventResource;
use App\Modules\Timetable\Services\SpecialEventService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SpecialEventController extends BaseCrudController
{
    public function __construct(private readonly SpecialEventService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SpecialEventResource::class;
    }

    public function store(SpecialEventRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SpecialEventRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
