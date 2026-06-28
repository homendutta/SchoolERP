<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\ExamTypeRequest;
use App\Modules\Examination\Http\Resources\ExamTypeResource;
use App\Modules\Examination\Services\ExamTypeService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ExamTypeController extends BaseCrudController
{
    public function __construct(private readonly ExamTypeService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamTypeResource::class;
    }

    public function store(ExamTypeRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ExamTypeRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
