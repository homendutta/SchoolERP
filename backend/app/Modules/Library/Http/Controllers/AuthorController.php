<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Requests\AuthorRequest;
use App\Modules\Library\Http\Resources\SimpleResource;
use App\Modules\Library\Services\AuthorService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class AuthorController extends BaseCrudController
{
    public function __construct(private readonly AuthorService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(AuthorRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(AuthorRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
