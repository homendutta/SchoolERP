<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\TermRequest;
use App\Modules\Academic\Http\Resources\TermResource;
use App\Modules\Academic\Services\TermService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class TermController extends BaseCrudController
{
    public function __construct(private readonly TermService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return TermResource::class;
    }

    public function store(TermRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TermRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
