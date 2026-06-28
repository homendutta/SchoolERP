<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\InstallmentRequest;
use App\Modules\Finance\Http\Resources\InstallmentResource;
use App\Modules\Finance\Services\InstallmentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class InstallmentController extends BaseCrudController
{
    public function __construct(private readonly InstallmentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return InstallmentResource::class;
    }

    public function store(InstallmentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(InstallmentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
