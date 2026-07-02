<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Http\Requests\VehicleDocumentRequest;
use App\Modules\Transport\Http\Resources\SimpleResource;
use App\Modules\Transport\Services\VehicleDocumentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/** Vehicle documents (Media references only). */
class VehicleDocumentController extends BaseCrudController
{
    public function __construct(private readonly VehicleDocumentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(VehicleDocumentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(VehicleDocumentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
