<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\SectionRequest;
use App\Modules\Academic\Http\Resources\SectionResource;
use App\Modules\Academic\Services\SectionService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class SectionController extends BaseCrudController
{
    public function __construct(private readonly SectionService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SectionResource::class;
    }

    public function store(SectionRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(SectionRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
