<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\ExamGradeRequest;
use App\Modules\Examination\Http\Resources\ExamGradeResource;
use App\Modules\Examination\Services\ExamGradeService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ExamGradeController extends BaseCrudController
{
    public function __construct(private readonly ExamGradeService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamGradeResource::class;
    }

    public function store(ExamGradeRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ExamGradeRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
