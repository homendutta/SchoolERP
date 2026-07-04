<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers;

use App\Modules\Integrations\Http\Requests\ProviderRequest;
use App\Modules\Integrations\Http\Resources\SimpleResource;
use App\Modules\Integrations\Models\Provider;
use App\Modules\Integrations\Services\IntegrationService;
use App\Modules\Integrations\Services\ProviderService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ProviderController extends BaseCrudController
{
    public function __construct(
        private readonly ProviderService $service,
        private readonly IntegrationService $integration,
    ) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(ProviderRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ProviderRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Run the provider's health check (through its adapter) and persist the result. */
    public function health(int|string $id): JsonResponse
    {
        $provider = Provider::query()->findOrFail($id);

        return $this->ok($this->integration->health($provider));
    }

    /** Run the provider's connectivity/config test (through its adapter). */
    public function test(int|string $id): JsonResponse
    {
        $provider = Provider::query()->findOrFail($id);

        return $this->ok($this->integration->test($provider), 'Provider tested.');
    }
}
