<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Http\Requests\PeriodRequest;
use App\Modules\Timetable\Http\Resources\PeriodResource;
use App\Modules\Timetable\Services\PeriodService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class PeriodController extends BaseCrudController
{
    public function __construct(private readonly PeriodService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return PeriodResource::class;
    }

    public function store(PeriodRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(PeriodRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
