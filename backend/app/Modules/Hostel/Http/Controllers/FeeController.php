<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Http\Requests\HostelFeeRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Services\HostelFeeService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class FeeController extends BaseCrudController
{
    public function __construct(private readonly HostelFeeService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(HostelFeeRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(HostelFeeRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
