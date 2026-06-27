<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Requests\MasterDataValueRequest;
use App\Modules\Administration\Http\Resources\MasterDataValueResource;
use App\Modules\Administration\Services\MasterDataValueService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class MasterDataValueController extends BaseCrudController
{
    public function __construct(private readonly MasterDataValueService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return MasterDataValueResource::class;
    }

    public function store(MasterDataValueRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(MasterDataValueRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
