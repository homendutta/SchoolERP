<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Http\Requests\SalaryRevisionRequest;
use App\Modules\Payroll\Http\Resources\SimpleResource;
use App\Modules\Payroll\Services\SalaryRevisionService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SalaryRevisionController extends BaseCrudController
{
    public function __construct(private readonly SalaryRevisionService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(SalaryRevisionRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SalaryRevisionRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
