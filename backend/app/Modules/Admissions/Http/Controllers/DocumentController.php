<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Http\Requests\DocumentRequest;
use App\Modules\Admissions\Http\Resources\DocumentResource;
use App\Modules\Admissions\Services\DocumentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class DocumentController extends BaseCrudController
{
    public function __construct(private readonly DocumentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return DocumentResource::class;
    }

    public function store(DocumentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(DocumentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
