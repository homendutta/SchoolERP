<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\FeeMasterRequest;
use App\Modules\Finance\Http\Resources\FeeMasterResource;
use App\Modules\Finance\Services\FeeMasterService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class FeeMasterController extends BaseCrudController
{
    public function __construct(private readonly FeeMasterService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return FeeMasterResource::class;
    }

    public function store(FeeMasterRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(FeeMasterRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
