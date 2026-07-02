<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Requests\BookRequest;
use App\Modules\Library\Http\Resources\BookResource;
use App\Modules\Library\Services\BookService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class BookController extends BaseCrudController
{
    public function __construct(private readonly BookService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return BookResource::class;
    }

    public function store(BookRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(BookRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
