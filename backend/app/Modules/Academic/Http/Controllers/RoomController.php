<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\RoomRequest;
use App\Modules\Academic\Http\Resources\RoomResource;
use App\Modules\Academic\Services\RoomService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class RoomController extends BaseCrudController
{
    public function __construct(private readonly RoomService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return RoomResource::class;
    }

    public function store(RoomRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(RoomRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
