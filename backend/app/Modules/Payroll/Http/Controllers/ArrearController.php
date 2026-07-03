<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Http\Requests\ArrearRequest;
use App\Modules\Payroll\Http\Resources\SimpleResource;
use App\Modules\Payroll\Services\ArrearService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ArrearController extends BaseCrudController
{
    public function __construct(private readonly ArrearService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(ArrearRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ArrearRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
