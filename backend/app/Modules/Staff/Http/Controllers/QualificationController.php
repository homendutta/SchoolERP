<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Http\Requests\QualificationRequest;
use App\Modules\Staff\Http\Resources\QualificationResource;
use App\Modules\Staff\Services\StaffQualificationService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class QualificationController extends BaseCrudController
{
    public function __construct(private readonly StaffQualificationService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return QualificationResource::class;
    }

    public function store(QualificationRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(QualificationRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
