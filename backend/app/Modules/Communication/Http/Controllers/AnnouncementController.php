<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Modules\Communication\Http\Requests\AnnouncementRequest;
use App\Modules\Communication\Http\Resources\AnnouncementResource;
use App\Modules\Communication\Services\AnnouncementService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends BaseCrudController
{
    public function __construct(private readonly AnnouncementService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return AnnouncementResource::class;
    }

    public function store(AnnouncementRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(AnnouncementRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
