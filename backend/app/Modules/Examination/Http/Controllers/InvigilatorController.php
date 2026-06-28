<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\InvigilatorRequest;
use App\Modules\Examination\Http\Resources\ExamInvigilatorResource;
use App\Modules\Examination\Services\InvigilatorService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class InvigilatorController extends BaseCrudController
{
    public function __construct(private readonly InvigilatorService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamInvigilatorResource::class;
    }

    public function store(InvigilatorRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(InvigilatorRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
