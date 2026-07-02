<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Http\Requests\VisitorRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Services\VisitorService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class VisitorController extends BaseCrudController
{
    public function __construct(private readonly VisitorService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(VisitorRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(VisitorRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
