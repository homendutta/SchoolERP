<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Http\Requests\SalaryAssignmentRequest;
use App\Modules\Payroll\Http\Resources\SimpleResource;
use App\Modules\Payroll\Services\SalaryAssignmentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SalaryAssignmentController extends BaseCrudController
{
    public function __construct(private readonly SalaryAssignmentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(SalaryAssignmentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SalaryAssignmentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
