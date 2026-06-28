<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Modules\Communication\Http\Requests\CircularRequest;
use App\Modules\Communication\Http\Resources\CircularResource;
use App\Modules\Communication\Services\CircularService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class CircularController extends BaseCrudController
{
    public function __construct(private readonly CircularService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return CircularResource::class;
    }

    public function store(CircularRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(CircularRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
