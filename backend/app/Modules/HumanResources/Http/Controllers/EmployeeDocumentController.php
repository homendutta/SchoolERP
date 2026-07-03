<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Requests\EmployeeDocumentRequest;
use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Services\EmployeeDocumentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class EmployeeDocumentController extends BaseCrudController
{
    public function __construct(private readonly EmployeeDocumentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(EmployeeDocumentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(EmployeeDocumentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
