<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\SubjectGroupRequest;
use App\Modules\Academic\Http\Resources\SubjectGroupResource;
use App\Modules\Academic\Services\SubjectGroupService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SubjectGroupController extends BaseCrudController
{
    public function __construct(private readonly SubjectGroupService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SubjectGroupResource::class;
    }

    public function store(SubjectGroupRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SubjectGroupRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
