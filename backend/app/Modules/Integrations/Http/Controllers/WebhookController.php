<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers;

use App\Modules\Integrations\Http\Requests\WebhookRequest;
use App\Modules\Integrations\Http\Resources\SimpleResource;
use App\Modules\Integrations\Services\WebhookService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class WebhookController extends BaseCrudController
{
    public function __construct(private readonly WebhookService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(WebhookRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(WebhookRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
