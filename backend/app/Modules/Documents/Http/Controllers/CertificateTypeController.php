<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Http\Requests\CertificateTypeRequest;
use App\Modules\Documents\Http\Resources\SimpleResource;
use App\Modules\Documents\Services\CertificateTypeService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class CertificateTypeController extends BaseCrudController
{
    public function __construct(private readonly CertificateTypeService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(CertificateTypeRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(CertificateTypeRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
