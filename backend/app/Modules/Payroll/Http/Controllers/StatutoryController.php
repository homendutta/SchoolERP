<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Http\Requests\StatutoryRequest;
use App\Modules\Payroll\Http\Resources\SimpleResource;
use App\Modules\Payroll\Services\StatutoryService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class StatutoryController extends BaseCrudController
{
    public function __construct(private readonly StatutoryService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(StatutoryRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(StatutoryRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
