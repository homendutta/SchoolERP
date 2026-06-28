<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\FineRuleRequest;
use App\Modules\Finance\Http\Resources\FineRuleResource;
use App\Modules\Finance\Services\FineRuleService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class FineRuleController extends BaseCrudController
{
    public function __construct(private readonly FineRuleService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return FineRuleResource::class;
    }

    public function store(FineRuleRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(FineRuleRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
