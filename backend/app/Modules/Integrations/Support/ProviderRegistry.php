<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Support;

use App\Platform\Shared\Exceptions\DomainException;

/**
 * The reusable provider registry (discovery + selection). Adapters register here;
 * the platform discovers them by code or category. New providers register without
 * any other part of the ERP changing — the extensibility guarantee.
 */
class ProviderRegistry
{
    /** @var array<string, IntegrationProvider> */
    private array $adapters = [];

    public function register(IntegrationProvider $adapter): void
    {
        $this->adapters[$adapter->code()] = $adapter;
    }

    public function has(string $code): bool
    {
        return isset($this->adapters[$code]);
    }

    public function get(string $code): IntegrationProvider
    {
        return $this->adapters[$code]
            ?? throw new DomainException("Integration adapter '{$code}' is not registered.", 404, 'ADAPTER_NOT_FOUND');
    }

    /** @return array<int, IntegrationProvider> */
    public function all(): array
    {
        return array_values($this->adapters);
    }

    /** @return array<int, IntegrationProvider> */
    public function byCategory(string $category): array
    {
        return array_values(array_filter($this->adapters, fn (IntegrationProvider $a) => $a->category() === $category));
    }

    /** @return array<int, string> */
    public function codes(): array
    {
        return array_keys($this->adapters);
    }
}
