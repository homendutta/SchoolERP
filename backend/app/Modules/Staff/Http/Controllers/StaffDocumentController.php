<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Http\Requests\StaffDocumentRequest;
use App\Modules\Staff\Http\Resources\StaffDocumentResource;
use App\Modules\Staff\Services\StaffDocumentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class StaffDocumentController extends BaseCrudController
{
    public function __construct(private readonly StaffDocumentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return StaffDocumentResource::class;
    }

    public function store(StaffDocumentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(StaffDocumentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
