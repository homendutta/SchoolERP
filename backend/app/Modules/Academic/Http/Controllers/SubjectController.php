<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\SubjectRequest;
use App\Modules\Academic\Http\Resources\SubjectResource;
use App\Modules\Academic\Services\SubjectService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SubjectController extends BaseCrudController
{
    public function __construct(private readonly SubjectService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SubjectResource::class;
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SubjectRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
