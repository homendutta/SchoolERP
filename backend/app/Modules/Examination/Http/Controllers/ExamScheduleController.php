<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\ExamScheduleRequest;
use App\Modules\Examination\Http\Resources\ExamScheduleResource;
use App\Modules\Examination\Services\ExamScheduleService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/** Schedule writes run clash detection inside the service. */
class ExamScheduleController extends BaseCrudController
{
    public function __construct(private readonly ExamScheduleService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamScheduleResource::class;
    }

    public function store(ExamScheduleRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ExamScheduleRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
