<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\SiblingRuleRequest;
use App\Modules\Finance\Http\Resources\SiblingRuleResource;
use App\Modules\Finance\Services\SiblingRuleService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SiblingRuleController extends BaseCrudController
{
    public function __construct(private readonly SiblingRuleService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SiblingRuleResource::class;
    }

    public function store(SiblingRuleRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SiblingRuleRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
