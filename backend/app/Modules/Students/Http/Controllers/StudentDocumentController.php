<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Http\Requests\StudentDocumentRequest;
use App\Modules\Students\Http\Resources\StudentDocumentResource;
use App\Modules\Students\Services\StudentDocumentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class StudentDocumentController extends BaseCrudController
{
    public function __construct(private readonly StudentDocumentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return StudentDocumentResource::class;
    }

    public function store(StudentDocumentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(StudentDocumentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
