<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Http\Requests\HostelRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Services\HostelService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class HostelController extends BaseCrudController
{
    public function __construct(private readonly HostelService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(HostelRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(HostelRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
