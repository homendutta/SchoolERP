<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Actions\SetCurrentAcademicYearAction;
use App\Modules\Academic\Http\Requests\AcademicYearRequest;
use App\Modules\Academic\Http\Resources\AcademicYearResource;
use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Services\AcademicYearService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends BaseCrudController
{
    public function __construct(private readonly AcademicYearService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return AcademicYearResource::class;
    }

    public function store(AcademicYearRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(AcademicYearRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    public function setCurrent(int|string $id, SetCurrentAcademicYearAction $action): JsonResponse
    {
        $year = $action->handle(AcademicYear::query()->findOrFail($id));

        return $this->ok(new AcademicYearResource($year), 'Academic year set as current.');
    }
}
