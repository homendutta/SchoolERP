<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Modules\Communication\Http\Requests\TemplateRequest;
use App\Modules\Communication\Http\Resources\TemplateResource;
use App\Modules\Communication\Services\TemplateService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class TemplateController extends BaseCrudController
{
    public function __construct(private readonly TemplateService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return TemplateResource::class;
    }

    public function store(TemplateRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TemplateRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
