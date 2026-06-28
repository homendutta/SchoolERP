<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\ExamComponentRequest;
use App\Modules\Examination\Http\Resources\ExamComponentResource;
use App\Modules\Examination\Services\ExamComponentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ExamComponentController extends BaseCrudController
{
    public function __construct(private readonly ExamComponentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamComponentResource::class;
    }

    public function store(ExamComponentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ExamComponentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
