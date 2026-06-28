<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\DiscountRequest;
use App\Modules\Finance\Http\Resources\DiscountResource;
use App\Modules\Finance\Services\DiscountService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class DiscountController extends BaseCrudController
{
    public function __construct(private readonly DiscountService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return DiscountResource::class;
    }

    public function store(DiscountRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(DiscountRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
