<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\HolidayTypeRequest;
use App\Modules\Academic\Http\Resources\HolidayTypeResource;
use App\Modules\Academic\Services\HolidayTypeService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class HolidayTypeController extends BaseCrudController
{
    public function __construct(private readonly HolidayTypeService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return HolidayTypeResource::class;
    }

    public function store(HolidayTypeRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(HolidayTypeRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
