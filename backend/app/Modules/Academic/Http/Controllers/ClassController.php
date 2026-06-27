<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\ClassRequest;
use App\Modules\Academic\Http\Resources\ClassResource;
use App\Modules\Academic\Services\SchoolClassService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ClassController extends BaseCrudController
{
    public function __construct(private readonly SchoolClassService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ClassResource::class;
    }

    public function store(ClassRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ClassRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
