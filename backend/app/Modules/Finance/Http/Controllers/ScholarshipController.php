<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Http\Requests\ScholarshipRequest;
use App\Modules\Finance\Http\Resources\ScholarshipResource;
use App\Modules\Finance\Services\ScholarshipService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ScholarshipController extends BaseCrudController
{
    public function __construct(private readonly ScholarshipService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ScholarshipResource::class;
    }

    public function store(ScholarshipRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ScholarshipRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
