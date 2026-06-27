<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\AcademicCalendarRequest;
use App\Modules\Academic\Http\Resources\AcademicCalendarResource;
use App\Modules\Academic\Services\AcademicCalendarService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class AcademicCalendarController extends BaseCrudController
{
    public function __construct(private readonly AcademicCalendarService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return AcademicCalendarResource::class;
    }

    public function store(AcademicCalendarRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(AcademicCalendarRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
