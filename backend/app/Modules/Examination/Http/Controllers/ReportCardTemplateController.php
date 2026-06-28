<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\ReportCardTemplateRequest;
use App\Modules\Examination\Http\Resources\ReportCardTemplateResource;
use App\Modules\Examination\Services\ReportCardTemplateService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ReportCardTemplateController extends BaseCrudController
{
    public function __construct(private readonly ReportCardTemplateService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ReportCardTemplateResource::class;
    }

    public function store(ReportCardTemplateRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ReportCardTemplateRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
