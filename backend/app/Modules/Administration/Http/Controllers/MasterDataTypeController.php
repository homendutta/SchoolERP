<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Requests\MasterDataTypeRequest;
use App\Modules\Administration\Http\Resources\MasterDataTypeResource;
use App\Modules\Administration\Services\MasterDataTypeService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class MasterDataTypeController extends BaseCrudController
{
    public function __construct(private readonly MasterDataTypeService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return MasterDataTypeResource::class;
    }

    public function store(MasterDataTypeRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(MasterDataTypeRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
