<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Enums\HealthStatus;
use App\Modules\Integrations\Enums\ProviderStatus;
use App\Modules\Integrations\Models\Provider;
use App\Modules\Integrations\Support\ProviderRegistry;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\DomainException;

/**
 * The single gateway between the ERP and third-party systems. Business modules
 * ask this service for a provider by CATEGORY (never a vendor); it selects the
 * enabled provider (default first, then priority) and resolves its registered
 * adapter. Health checks + tests run through the adapter and are audited.
 */
class IntegrationService
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly ActivityLogger $activity,
    ) {}

    /** Select the provider to use for a category (default → highest priority → enabled). */
    public function providerFor(int $schoolId, string $category): ?Provider
    {
        return Provider::query()->where('school_id', $schoolId)
            ->whereHas('category', fn ($q) => $q->where('code', $category))
            ->where('status', ProviderStatus::Enabled->value)
            ->orderByDesc('is_default')->orderBy('priority')->first();
    }

    /**
     * Run a provider's health check through its adapter and persist the result.
     *
     * @return array{status:string, detail?:string}
     */
    public function health(Provider $provider): array
    {
        $adapterCode = $provider->adapter ?: $provider->code;
        if (! $this->registry->has($adapterCode)) {
            $provider->update(['health' => HealthStatus::Unknown->value, 'last_checked_at' => now()]);

            return ['status' => 'unknown', 'detail' => 'No adapter registered.'];
        }

        $config = array_merge($provider->config ?? [], ['school_id' => $provider->school_id]);
        $result = $this->registry->get($adapterCode)->healthCheck($config);

        $provider->update(['health' => $result['status'], 'last_checked_at' => now()]);
        $this->activity->record('integrations.health_checked', "Health of {$provider->name}: {$result['status']}", $provider, [
            'health' => $result['status'],
        ], (int) $provider->school_id, 'integrations');

        return $result;
    }

    /**
     * Run a provider's connectivity/config test through its adapter.
     *
     * @return array<string, mixed>
     */
    public function test(Provider $provider): array
    {
        $adapterCode = $provider->adapter ?: $provider->code;
        if (! $this->registry->has($adapterCode)) {
            throw new DomainException('No adapter is registered for this provider.', 422, 'NO_ADAPTER');
        }

        $config = array_merge($provider->config ?? [], ['school_id' => $provider->school_id]);
        $result = $this->registry->get($adapterCode)->test($config);

        $this->activity->record('integrations.provider_tested', "Tested {$provider->name}", $provider, [], (int) $provider->school_id, 'integrations');

        return $result;
    }

    /**
     * The catalog of registered adapters (discovery).
     *
     * @return array<int, array<string, mixed>>
     */
    public function adapters(): array
    {
        return array_map(fn ($a) => [
            'code' => $a->code(),
            'category' => $a->category(),
            'name' => $a->name(),
        ], $this->registry->all());
    }
}
