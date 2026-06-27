<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Requests\MasterDataGroupRequest;
use App\Modules\Administration\Http\Resources\MasterDataGroupResource;
use App\Modules\Administration\Services\MasterDataGroupService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class MasterDataGroupController extends BaseCrudController
{
    public function __construct(private readonly MasterDataGroupService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return MasterDataGroupResource::class;
    }

    public function store(MasterDataGroupRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(MasterDataGroupRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
