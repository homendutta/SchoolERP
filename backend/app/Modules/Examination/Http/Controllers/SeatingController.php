<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\SeatingRequest;
use App\Modules\Examination\Http\Resources\ExamSeatAllocationResource;
use App\Modules\Examination\Services\SeatingService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SeatingController extends BaseCrudController
{
    public function __construct(private readonly SeatingService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamSeatAllocationResource::class;
    }

    public function store(SeatingRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SeatingRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
