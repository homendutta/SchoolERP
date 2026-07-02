<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Requests\CopyRequest;
use App\Modules\Library\Http\Resources\CopyResource;
use App\Modules\Library\Services\CopyService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class CopyController extends BaseCrudController
{
    public function __construct(private readonly CopyService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return CopyResource::class;
    }

    public function store(CopyRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(CopyRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
