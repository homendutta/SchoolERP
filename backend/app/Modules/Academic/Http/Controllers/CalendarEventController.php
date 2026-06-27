<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\CalendarEventRequest;
use App\Modules\Academic\Http\Resources\CalendarEventResource;
use App\Modules\Academic\Services\CalendarEventService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class CalendarEventController extends BaseCrudController
{
    public function __construct(private readonly CalendarEventService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return CalendarEventResource::class;
    }

    public function store(CalendarEventRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(CalendarEventRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
