<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Requests\FineRuleRequest;
use App\Modules\Library\Http\Resources\SimpleResource;
use App\Modules\Library\Services\FineRuleService;
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
        return SimpleResource::class;
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
