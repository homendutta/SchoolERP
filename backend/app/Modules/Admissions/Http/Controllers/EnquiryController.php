<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Http\Requests\EnquiryRequest;
use App\Modules\Admissions\Http\Resources\EnquiryResource;
use App\Modules\Admissions\Services\EnquiryService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class EnquiryController extends BaseCrudController
{
    public function __construct(private readonly EnquiryService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return EnquiryResource::class;
    }

    public function store(EnquiryRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(EnquiryRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
